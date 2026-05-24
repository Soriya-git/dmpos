<?php

use App\Services\MembershipCardLedger;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_card_balances', function (Blueprint $table) {
            if (! Schema::hasColumn('membership_card_balances', 'last_transaction_id')) {
                $table->foreignId('last_transaction_id')
                    ->nullable()
                    ->after('balance')
                    ->constrained('membership_card_transactions')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('membership_card_balances', 'ledger_verified_at')) {
                $table->timestamp('ledger_verified_at')->nullable()->after('last_transaction_id');
            }
        });

        Schema::table('membership_card_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('membership_card_transactions', 'ledger_sequence')) {
                $table->unsignedBigInteger('ledger_sequence')->nullable()->after('note');
            }

            if (! Schema::hasColumn('membership_card_transactions', 'previous_hash')) {
                $table->char('previous_hash', 64)->nullable()->after('ledger_sequence');
            }

            if (! Schema::hasColumn('membership_card_transactions', 'entry_hash')) {
                $table->char('entry_hash', 64)->nullable()->after('previous_hash');
            }

            if (! Schema::hasColumn('membership_card_transactions', 'signature_version')) {
                $table->unsignedSmallInteger('signature_version')->default(1)->after('entry_hash');
            }
        });

        $ledger = app(MembershipCardLedger::class);

        DB::table('membership_card_balances')
            ->orderBy('id')
            ->get()
            ->each(function ($balance): void {
                $transactions = DB::table('membership_card_transactions')
                    ->where('membership_card_id', $balance->membership_card_id)
                    ->where('currency', $balance->currency)
                    ->orderBy('id')
                    ->get();
                $firstTransaction = $transactions->first();
                $openingAmount = $firstTransaction
                    ? round((float) $firstTransaction->balance_before, 2)
                    : round((float) $balance->balance, 2);

                if (abs($openingAmount) < 0.009) {
                    return;
                }

                DB::table('membership_card_transactions')->insert([
                    'company_id' => DB::table('membership_cards')->where('id', $balance->membership_card_id)->value('company_id'),
                    'branch_id' => DB::table('membership_cards')->where('id', $balance->membership_card_id)->value('branch_id'),
                    'membership_card_id' => $balance->membership_card_id,
                    'customer_id' => DB::table('membership_cards')->where('id', $balance->membership_card_id)->value('customer_id'),
                    'transaction_no' => 'MCT-OPEN-'.$balance->membership_card_id.'-'.$balance->currency,
                    'transaction_type' => 'adjustment',
                    'direction' => $openingAmount >= 0 ? 'credit' : 'debit',
                    'currency' => $balance->currency,
                    'amount' => abs($openingAmount),
                    'promotion_amount' => 0,
                    'balance_before' => 0,
                    'balance_after' => $openingAmount,
                    'exchange_rate_snapshot' => 4100,
                    'amount_usd_equivalent' => strtoupper($balance->currency) === 'KHR' ? abs($openingAmount) / 4100 : abs($openingAmount),
                    'amount_khr_equivalent' => strtoupper($balance->currency) === 'KHR' ? abs($openingAmount) : abs($openingAmount) * 4100,
                    'transacted_at' => $firstTransaction?->transacted_at ?? $firstTransaction?->created_at ?? now(),
                    'payload' => json_encode(['legacy_opening_balance' => true]),
                    'note' => 'Opening balance generated while hardening membership card ledger.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        DB::table('membership_card_transactions')
            ->orderBy('membership_card_id')
            ->orderBy('currency')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($transaction) => $transaction->membership_card_id.'|'.$transaction->currency)
            ->each(function ($transactions) use ($ledger): void {
                $previousHash = null;
                $sequence = 1;
                $orderedTransactions = $transactions->sortBy(fn ($transaction) => [
                    str_contains((string) $transaction->transaction_no, 'MCT-OPEN-') ? 0 : 1,
                    $transaction->id,
                ]);

                foreach ($orderedTransactions as $transaction) {
                    $data = (array) $transaction;
                    $data['ledger_sequence'] = $sequence;
                    $data['previous_hash'] = $previousHash;
                    $data['signature_version'] = $data['signature_version'] ?? 1;
                    $data['entry_hash'] = $ledger->hashFor($data);

                    DB::table('membership_card_transactions')
                        ->where('id', $transaction->id)
                        ->update([
                            'ledger_sequence' => $sequence,
                            'previous_hash' => $previousHash,
                            'entry_hash' => $data['entry_hash'],
                            'signature_version' => $data['signature_version'],
                        ]);

                    $previousHash = $data['entry_hash'];
                    $sequence++;
                }
            });

        DB::table('membership_card_balances')
            ->orderBy('id')
            ->get()
            ->each(function ($balance) use ($ledger): void {
                $actual = $ledger->calculatedBalance((int) $balance->membership_card_id, $balance->currency);
                $lastTransactionId = DB::table('membership_card_transactions')
                    ->where('membership_card_id', $balance->membership_card_id)
                    ->where('currency', $balance->currency)
                    ->orderByDesc('ledger_sequence')
                    ->orderByDesc('id')
                    ->value('id');

                DB::table('membership_card_balances')
                    ->where('id', $balance->id)
                    ->update([
                        'balance' => $actual,
                        'last_transaction_id' => $lastTransactionId,
                        'ledger_verified_at' => now(),
                    ]);
            });

        Schema::table('membership_card_transactions', function (Blueprint $table) {
            $table->unique(['membership_card_id', 'currency', 'ledger_sequence'], 'member_card_tx_card_currency_sequence_unique');
            $table->index(['membership_card_id', 'currency', 'entry_hash'], 'member_card_tx_card_currency_hash_idx');
        });

        $this->createImmutableLedgerTriggers();
    }

    public function down(): void
    {
        $this->dropImmutableLedgerTriggers();

        Schema::table('membership_card_transactions', function (Blueprint $table) {
            $table->dropUnique('member_card_tx_card_currency_sequence_unique');
            $table->dropIndex('member_card_tx_card_currency_hash_idx');
        });

        Schema::table('membership_card_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'ledger_sequence',
                'previous_hash',
                'entry_hash',
                'signature_version',
            ]);
        });

        Schema::table('membership_card_balances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_transaction_id');
            $table->dropColumn('ledger_verified_at');
        });
    }

    private function createImmutableLedgerTriggers(): void
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $this->dropImmutableLedgerTriggers();

        DB::unprepared(<<<'SQL'
CREATE TRIGGER membership_card_transactions_no_update
BEFORE UPDATE ON membership_card_transactions
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'membership_card_transactions is immutable: update rejected';
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER membership_card_transactions_no_delete
BEFORE DELETE ON membership_card_transactions
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'membership_card_transactions is immutable: delete rejected';
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER membership_card_balances_verify_insert
BEFORE INSERT ON membership_card_balances
FOR EACH ROW
BEGIN
    DECLARE ledger_balance DECIMAL(16, 2);

    SELECT COALESCE(SUM(CASE
        WHEN direction = 'credit' THEN amount + promotion_amount
        ELSE -amount
    END), 0)
    INTO ledger_balance
    FROM membership_card_transactions
    WHERE membership_card_id = NEW.membership_card_id
      AND currency = NEW.currency;

    IF ABS(NEW.balance - ledger_balance) > 0.009 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'membership_card_balances cache does not match ledger';
    END IF;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER membership_card_balances_verify_update
BEFORE UPDATE ON membership_card_balances
FOR EACH ROW
BEGIN
    DECLARE ledger_balance DECIMAL(16, 2);

    SELECT COALESCE(SUM(CASE
        WHEN direction = 'credit' THEN amount + promotion_amount
        ELSE -amount
    END), 0)
    INTO ledger_balance
    FROM membership_card_transactions
    WHERE membership_card_id = NEW.membership_card_id
      AND currency = NEW.currency;

    IF ABS(NEW.balance - ledger_balance) > 0.009 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'membership_card_balances cache does not match ledger';
    END IF;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER membership_card_balances_no_delete
BEFORE DELETE ON membership_card_balances
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'membership_card_balances is protected: delete rejected';
END
SQL);
    }

    private function dropImmutableLedgerTriggers(): void
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS membership_card_transactions_no_update');
        DB::unprepared('DROP TRIGGER IF EXISTS membership_card_transactions_no_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS membership_card_balances_verify_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS membership_card_balances_verify_update');
        DB::unprepared('DROP TRIGGER IF EXISTS membership_card_balances_no_delete');
    }
};
