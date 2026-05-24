<?php

namespace App\Http\Controllers\MembershipCard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MembershipCard;
use App\Models\MembershipCardBalance;
use App\Models\MembershipCardTransaction;
use App\Services\MembershipCardLedger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CustomerCardController extends Controller
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->company_id;
        $branchId = $request->user()?->branch_id;
        $cardId = $request->integer('card');

        $cards = MembershipCard::query()
            ->with(['customer', 'branch', 'balances', 'transactions' => fn ($query) => $query->latest('transacted_at')->latest('id')->limit(5)])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($cardId > 0, fn ($query) => $query->where('id', $cardId))
            ->where(function ($query) use ($branchId) {
                $query->whereNull('branch_id')
                    ->when($branchId, fn ($branchQuery) => $branchQuery->orWhere('branch_id', $branchId));
            })
            ->orderBy('card_no')
            ->get()
            ->map(fn (MembershipCard $card) => $this->formatCard($card));

        return Inertia::render('MembershipCard/CustomerCard', [
            'cards' => $cards,
            'filters' => [
                'card' => $cardId ?: null,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()?->company_id;
        $branchId = $request->user()?->branch_id;

        $customers = Customer::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->where('is_active', true)
            ->where(function ($query) use ($branchId) {
                $query->whereNull('branch_id')
                    ->when($branchId, fn ($branchQuery) => $branchQuery->orWhere('branch_id', $branchId));
            })
            ->orderBy('name')
            ->limit(300)
            ->get(['id', 'name', 'phone_number', 'branch_id'])
            ->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->name ?: 'Unnamed Customer',
                'phone' => $customer->phone_number,
                'branchId' => $customer->branch_id,
            ])
            ->values()
            ->all();

        return Inertia::render('MembershipCard/Create', [
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->user()?->company_id;
        $branchId = $request->user()?->branch_id;

        $data = $request->validate([
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')->where(fn ($query) => $query
                    ->when($companyId, fn ($customerQuery) => $customerQuery->where('company_id', $companyId))
                    ->where('is_active', true)),
            ],
            'card_no' => ['required', 'string', 'max:255', 'unique:membership_cards,card_no'],
            'card_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive,blocked,expired,cancelled'],
            'issued_date' => ['nullable', 'date'],
            'expired_date' => ['nullable', 'date', 'after_or_equal:issued_date'],
            'remark' => ['nullable', 'string', 'max:5000'],
        ]);

        $customer = Customer::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->where('id', $data['customer_id'])
            ->firstOrFail();

        abort_if($branchId && $customer->branch_id && (int) $customer->branch_id !== (int) $branchId, 403);

        $card = $customer->membershipCards()->create([
            'company_id' => $customer->company_id,
            'branch_id' => $customer->branch_id,
            'card_no' => $data['card_no'],
            'card_name' => $data['card_name'] ?? null,
            'status' => $data['status'],
            'issued_date' => $data['issued_date'] ?? null,
            'expired_date' => $data['expired_date'] ?? null,
            'remark' => $data['remark'] ?? null,
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('membership-cards.index', ['card' => $card->id])
            ->with('success', 'Membership card created.');
    }

    public function transactions(Request $request, MembershipCard $membershipCard): Response
    {
        $this->ensureCardAccess($request, $membershipCard);

        $membershipCard->load(['customer', 'branch', 'balances']);

        $transactions = $membershipCard->transactions()
            ->with(['invoice:id,invoice_no', 'payment:id,payment_no'])
            ->latest('transacted_at')
            ->latest('id')
            ->get()
            ->map(fn (MembershipCardTransaction $transaction) => $this->formatTransaction($transaction));

        return Inertia::render('MembershipCard/CustomerTransactionDetial', [
            'card' => $this->formatCard($membershipCard),
            'transactions' => $transactions,
        ]);
    }

    public function topup(Request $request, MembershipCard $membershipCard): Response
    {
        $this->ensureCardAccess($request, $membershipCard);

        $membershipCard->load(['customer', 'branch', 'balances']);

        return Inertia::render('MembershipCard/CustomerCardTopup', [
            'card' => $this->formatCard($membershipCard),
        ]);
    }

    public function storeTopup(Request $request, MembershipCard $membershipCard)
    {
        $this->ensureCardAccess($request, $membershipCard);

        $data = $request->validate([
            'currency' => ['required', 'string', 'size:3'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'promotion_amount' => ['nullable', 'numeric', 'min:0'],
            'promotion_name' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $currency = strtoupper($data['currency']);
        $amount = (float) $data['amount'];
        $promotionAmount = (float) ($data['promotion_amount'] ?? 0);
        $totalCredit = $amount + $promotionAmount;
        $exchangeRate = 4100;
        $amountUsd = $currency === 'KHR' ? $totalCredit / $exchangeRate : $totalCredit;

        try {
            app(MembershipCardLedger::class)->credit($membershipCard, [
                'transaction_type' => 'recharge',
                'currency' => $currency,
                'amount' => $amount,
                'promotion_amount' => $promotionAmount,
                'promotion_name' => $data['promotion_name'] ?? null,
                'exchange_rate_snapshot' => $exchangeRate,
                'amount_usd_equivalent' => $amountUsd,
                'amount_khr_equivalent' => $amountUsd * $exchangeRate,
                'transacted_at' => now(),
                'performed_by' => $request->user()?->id,
                'note' => $data['note'] ?? null,
            ]);
        } catch (\RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }

        return redirect()
            ->route('membership-cards.index')
            ->with('success', 'Membership card topped up.');
    }

    private function ensureCardAccess(Request $request, MembershipCard $membershipCard): void
    {
        $companyId = $request->user()?->company_id;
        $branchId = $request->user()?->branch_id;

        abort_if($companyId && (int) $membershipCard->company_id !== (int) $companyId, 403);
        abort_if($branchId && $membershipCard->branch_id && (int) $membershipCard->branch_id !== (int) $branchId, 403);
    }

    private function formatCard(MembershipCard $card): array
    {
        return [
            'id' => $card->id,
            'cardNo' => $card->card_no,
            'cardName' => $card->card_name ?: 'Member Card',
            'status' => $card->status,
            'customerName' => $card->customer?->name ?: 'Unnamed Customer',
            'customerPhone' => $card->customer?->phone_number,
            'branchName' => $card->branch?->name,
            'issuedDate' => $card->issued_date?->toDateString(),
            'expiredDate' => $card->expired_date?->toDateString(),
            'remark' => $card->remark,
            'balances' => $card->balances
                ->map(fn (MembershipCardBalance $balance) => [
                    'currency' => $balance->currency,
                    'balance' => (float) $balance->balance,
                ])
                ->values()
                ->all(),
            'transactions' => $card->transactions
                ->map(fn (MembershipCardTransaction $transaction) => $this->formatTransaction($transaction))
                ->values()
                ->all(),
        ];
    }

    private function formatTransaction(MembershipCardTransaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'transactionNo' => $transaction->transaction_no,
            'type' => $transaction->transaction_type,
            'direction' => $transaction->direction,
            'currency' => $transaction->currency,
            'amount' => (float) $transaction->amount,
            'promotionAmount' => (float) $transaction->promotion_amount,
            'promotionName' => $transaction->promotion_name,
            'balanceBefore' => (float) $transaction->balance_before,
            'balanceAfter' => (float) $transaction->balance_after,
            'invoiceNo' => $transaction->invoice?->invoice_no,
            'paymentNo' => $transaction->payment?->payment_no,
            'note' => $transaction->note,
            'transactedAt' => $transaction->transacted_at?->format('Y-m-d H:i') ?? $transaction->created_at?->format('Y-m-d H:i'),
        ];
    }
}
