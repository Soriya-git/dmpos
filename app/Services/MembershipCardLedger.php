<?php

namespace App\Services;

use App\Models\MembershipCard;
use App\Models\MembershipCardBalance;
use App\Models\MembershipCardTransaction;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MembershipCardLedger
{
    public function credit(MembershipCard $card, array $attributes): MembershipCardTransaction
    {
        return $this->append($card, array_merge($attributes, ['direction' => 'credit']));
    }

    public function debit(MembershipCard $card, array $attributes): MembershipCardTransaction
    {
        return $this->append($card, array_merge($attributes, ['direction' => 'debit']));
    }

    public function append(MembershipCard $card, array $attributes): MembershipCardTransaction
    {
        return DB::transaction(function () use ($card, $attributes): MembershipCardTransaction {
            $currency = strtoupper((string) ($attributes['currency'] ?? 'USD'));
            $amount = round((float) ($attributes['amount'] ?? 0), 2);
            $promotionAmount = round((float) ($attributes['promotion_amount'] ?? 0), 2);
            $direction = $attributes['direction'] ?? null;

            if (! in_array($direction, ['credit', 'debit'], true)) {
                throw new RuntimeException('Membership card ledger direction must be credit or debit.');
            }

            if ($amount <= 0 && $promotionAmount <= 0) {
                throw new RuntimeException('Membership card ledger amount must be greater than zero.');
            }

            $balance = MembershipCardBalance::query()
                ->where('membership_card_id', $card->id)
                ->where('currency', $currency)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                $balance = MembershipCardBalance::create([
                    'membership_card_id' => $card->id,
                    'currency' => $currency,
                    'balance' => 0,
                    'ledger_verified_at' => now(),
                ]);
            }

            $lastTransaction = MembershipCardTransaction::query()
                ->where('membership_card_id', $card->id)
                ->where('currency', $currency)
                ->lockForUpdate()
                ->orderByDesc('ledger_sequence')
                ->orderByDesc('id')
                ->first();

            $this->assertLastHashIsValid($lastTransaction);

            $ledgerBalance = $this->calculatedBalance($card->id, $currency);
            $cachedBalance = round((float) $balance->balance, 2);

            if (abs($ledgerBalance - $cachedBalance) > 0.009) {
                throw new RuntimeException('Membership card balance cache does not match the immutable ledger.');
            }

            $signedAmount = $direction === 'credit'
                ? $amount + $promotionAmount
                : -$amount;
            $balanceAfter = round($ledgerBalance + $signedAmount, 2);

            if ($balanceAfter < -0.009) {
                throw new RuntimeException('Membership card balance is not enough for this payment.');
            }

            $payload = array_merge([
                'company_id' => $card->company_id,
                'branch_id' => $attributes['branch_id'] ?? $card->branch_id,
                'membership_card_id' => $card->id,
                'customer_id' => $card->customer_id,
                'transaction_no' => $attributes['transaction_no'] ?? DocumentNumber::make(MembershipCardTransaction::class, 'transaction_no', 'MCT', $attributes['branch_id'] ?? $card->branch_id),
                'transaction_type' => $attributes['transaction_type'],
                'direction' => $direction,
                'currency' => $currency,
                'amount' => $amount,
                'promotion_amount' => $promotionAmount,
                'promotion_name' => $attributes['promotion_name'] ?? null,
                'balance_before' => $ledgerBalance,
                'balance_after' => max(0, $balanceAfter),
                'exchange_rate_snapshot' => round((float) ($attributes['exchange_rate_snapshot'] ?? 4100), 4),
                'amount_usd_equivalent' => round((float) ($attributes['amount_usd_equivalent'] ?? 0), 2),
                'amount_khr_equivalent' => round((float) ($attributes['amount_khr_equivalent'] ?? 0), 2),
                'transacted_at' => $attributes['transacted_at'] ?? now(),
                'performed_by' => $attributes['performed_by'] ?? null,
                'payload' => $attributes['payload'] ?? null,
                'note' => $attributes['note'] ?? null,
                'ledger_sequence' => ((int) ($lastTransaction?->ledger_sequence ?? 0)) + 1,
                'previous_hash' => $lastTransaction?->entry_hash,
                'signature_version' => 1,
            ], collect($attributes)->only(['invoice_id', 'payment_id'])->all());

            $payload['entry_hash'] = $this->hashFor($payload);

            $transaction = MembershipCardTransaction::create($payload);

            $balance->forceFill([
                'balance' => max(0, $balanceAfter),
                'last_transaction_id' => $transaction->id,
                'ledger_verified_at' => now(),
            ])->save();

            return $transaction;
        });
    }

    public function assertBalanceIsVerified(MembershipCardBalance $balance): void
    {
        $this->assertLedgerIntegrity((int) $balance->membership_card_id, $balance->currency);

        $actual = $this->calculatedBalance((int) $balance->membership_card_id, $balance->currency);

        if (abs($actual - (float) $balance->balance) > 0.009) {
            throw new RuntimeException('Membership card balance cache does not match the immutable ledger.');
        }
    }

    public function assertLedgerIntegrity(int $membershipCardId, string $currency): void
    {
        $previousHash = null;

        MembershipCardTransaction::query()
            ->where('membership_card_id', $membershipCardId)
            ->where('currency', strtoupper($currency))
            ->orderBy('ledger_sequence')
            ->orderBy('id')
            ->each(function (MembershipCardTransaction $transaction) use (&$previousHash): void {
                if ($transaction->previous_hash !== $previousHash) {
                    throw new RuntimeException('Membership card ledger hash chain is broken.');
                }

                if ($transaction->entry_hash !== $this->hashFor($transaction->getAttributes())) {
                    throw new RuntimeException('Membership card ledger entry hash is invalid.');
                }

                $previousHash = $transaction->entry_hash;
            });
    }

    public function calculatedBalance(int $membershipCardId, string $currency): float
    {
        $transactions = MembershipCardTransaction::query()
            ->where('membership_card_id', $membershipCardId)
            ->where('currency', strtoupper($currency))
            ->get(['direction', 'amount', 'promotion_amount']);

        return round($transactions->sum(function (MembershipCardTransaction $transaction): float {
            if ($transaction->direction === 'credit') {
                return (float) $transaction->amount + (float) $transaction->promotion_amount;
            }

            return -1 * (float) $transaction->amount;
        }), 2);
    }

    public function hashFor(array $data): string
    {
        return hash_hmac(
            'sha256',
            json_encode($this->hashPayload($data), JSON_UNESCAPED_SLASHES),
            $this->signingKey(),
        );
    }

    private function assertLastHashIsValid(?MembershipCardTransaction $transaction): void
    {
        if (! $transaction) {
            return;
        }

        if ($transaction->entry_hash !== $this->hashFor($transaction->getAttributes())) {
            throw new RuntimeException('Membership card ledger latest hash is invalid.');
        }
    }

    private function hashPayload(array $data): array
    {
        $payload = [];

        foreach ([
            'company_id',
            'branch_id',
            'membership_card_id',
            'customer_id',
            'invoice_id',
            'payment_id',
            'transaction_no',
            'transaction_type',
            'direction',
            'currency',
            'amount',
            'promotion_amount',
            'promotion_name',
            'balance_before',
            'balance_after',
            'exchange_rate_snapshot',
            'amount_usd_equivalent',
            'amount_khr_equivalent',
            'transacted_at',
            'performed_by',
            'payload',
            'note',
            'ledger_sequence',
            'previous_hash',
            'signature_version',
        ] as $key) {
            $value = $data[$key] ?? null;

            if (in_array($key, ['amount', 'promotion_amount', 'balance_before', 'balance_after', 'amount_usd_equivalent', 'amount_khr_equivalent'], true)) {
                $value = number_format((float) $value, 2, '.', '');
            }

            if ($key === 'exchange_rate_snapshot') {
                $value = number_format((float) $value, 4, '.', '');
            }

            if ($key === 'transacted_at' && $value) {
                $value = is_string($value) ? date('Y-m-d H:i:s', strtotime($value)) : $value->format('Y-m-d H:i:s');
            }

            if ($key === 'payload' && is_string($value)) {
                $value = json_decode($value, true);
            }

            $payload[$key] = $value;
        }

        return $payload;
    }

    private function signingKey(): string
    {
        $key = (string) config('app.key');

        return str_starts_with($key, 'base64:')
            ? base64_decode(substr($key, 7)) ?: $key
            : $key;
    }
}
