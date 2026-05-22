<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PosSession;
use App\Models\PosTerminal;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SaleInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $activePosSession = $this->activePosSession($request);

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first.');
        }

        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $search = isset($filters['search'])
            ? ltrim(trim((string) $filters['search']), '#')
            : null;

        $invoiceModels = Invoice::query()
            ->with([
                'customer',
                'issuer:id,name',
                'lines.order.creator:id,name',
                'payments.paymentMethod',
                'payments.receiver:id,name',
                'posTerminal',
                'diningSession.diningResource',
            ])
            ->where('branch_id', $activePosSession->branch_id)
            ->where('status', '!=', 'cancelled')
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customer) use ($search) {
                            $customer->where('name', 'like', "%{$search}%")
                                ->orWhere('phone_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('diningSession.diningResource', fn ($resource) => $resource->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('posTerminal', fn ($terminal) => $terminal->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->get();

        $invoices = $invoiceModels->map(fn (Invoice $invoice) => $this->formatInvoice($invoice));

        return Inertia::render('Sales/Index', [
            'posSession' => [
                'id' => $activePosSession->id,
                'session_no' => $activePosSession->session_no ?? $activePosSession->session_number ?? ('POS-'.$activePosSession->id),
                'opening_cash_usd' => (float) $activePosSession->opening_cash_usd,
                'opening_cash_khr' => (float) $activePosSession->opening_cash_khr,
            ],
            'invoices' => $invoices,
            'paymentMethods' => $this->paymentMethodsFor($activePosSession),
            'paymentSummary' => $this->paymentSummary($invoiceModels, $activePosSession),
            'filters' => [
                'start_date' => $filters['start_date'] ?? null,
                'end_date' => $filters['end_date'] ?? null,
                'search' => $filters['search'] ?? null,
            ],
        ]);
    }

    public function receive(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'method' => ['required', 'string', 'max:50'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'currency' => ['required', 'in:USD,KHR'],
            'received_amount' => ['nullable', 'numeric', 'min:0'],
            'operation_status' => ['required', 'in:invoice_receipt_done'],
            'change_usd_amount' => ['nullable', 'integer', 'min:0'],
            'change_khr_amount' => ['nullable', 'integer', 'min:0'],
        ]);

        $activePosSession = $this->activePosSession($request);

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first.');
        }

        abort_if($invoice->branch_id !== $activePosSession->branch_id, 403);
        abort_if(in_array($invoice->status, ['cancelled'], true), 422, 'This invoice cannot receive payment.');
        abort_if((float) $invoice->balance_amount <= 0, 422, 'This invoice has no balance to receive.');

        DB::transaction(function () use ($invoice, $activePosSession, $data, $request) {
            $invoice->refresh();
            $balanceUsd = (float) $invoice->balance_amount;
            $exchangeRate = (float) ($invoice->exchange_rate_snapshot ?: 4100);
            $method = $this->paymentMethodFor(
                $data['method'],
                $data['currency'],
                $activePosSession,
                $data['payment_method_id'] ?? null,
            );

            abort_if(! $method, 422, 'Please select a valid payment method.');

            $isCash = str_starts_with($data['method'], 'cash-');
            $targetAmount = $data['currency'] === 'KHR' ? $balanceUsd * $exchangeRate : $balanceUsd;
            $receivedAmount = (float) ($data['received_amount'] ?? $targetAmount);
            $changeUsdAmount = $isCash ? (float) floor((float) ($data['change_usd_amount'] ?? 0)) : 0;
            $changeKhrAmount = $isCash ? $this->roundDownKhrChange((float) ($data['change_khr_amount'] ?? 0)) : 0;

            abort_if($receivedAmount <= 0, 422, 'Payment amount must be greater than zero.');

            if ($isCash) {
                $overpaidUsd = $data['currency'] === 'USD'
                    ? max(0, $receivedAmount - $targetAmount)
                    : max(0, ($receivedAmount - $targetAmount) / $exchangeRate);
                $changeUsdEquivalent = $changeUsdAmount + ($changeKhrAmount / $exchangeRate);
                $roundingToleranceUsd = 99 / $exchangeRate;

                abort_if(
                    $changeUsdEquivalent > $overpaidUsd + $roundingToleranceUsd + 0.01,
                    422,
                    'Change amount cannot be greater than the overpaid amount.'
                );
            }

            $netReceivedUsd = $data['currency'] === 'KHR'
                ? ($receivedAmount - $changeKhrAmount - ($changeUsdAmount * $exchangeRate)) / $exchangeRate
                : $receivedAmount - $changeUsdAmount - ($changeKhrAmount / $exchangeRate);
            $paidUsd = round(min($balanceUsd, max(0, $netReceivedUsd)), 2);

            abort_if($paidUsd <= 0, 422, 'Payment amount must be greater than zero.');

            $amountPaid = $data['currency'] === 'KHR' ? $paidUsd * $exchangeRate : $paidUsd;

            Payment::create([
                'company_id' => $invoice->company_id,
                'branch_id' => $invoice->branch_id,
                'invoice_id' => $invoice->id,
                'payment_method_id' => $method?->id,
                'payment_no' => DocumentNumber::make(Payment::class, 'payment_no', 'PY'),
                'status' => $paidUsd >= $balanceUsd ? 'paid' : 'partial',
                'currency' => $data['currency'],
                'amount_paid' => $amountPaid,
                'received_amount' => $receivedAmount,
                'change_usd_amount' => floor($changeUsdAmount),
                'change_khr_amount' => $changeKhrAmount,
                'exchange_rate_snapshot' => $exchangeRate,
                'amount_usd_equivalent' => $paidUsd,
                'amount_khr_equivalent' => $paidUsd * $exchangeRate,
                'paid_at' => now(),
                'received_by' => $request->user()->id,
            ]);

            $this->syncInvoicePaymentStatus($invoice);

            $this->recordReceivedPaymentTotals(
                $activePosSession,
                $data['method'],
                $data['currency'],
                $amountPaid,
                $receivedAmount,
                $changeUsdAmount,
                $changeKhrAmount,
            );

            $invoice->refresh();
            $invoice->diningSession?->update([
                'status' => $invoice->status === 'paid' ? 'paid' : 'invoiced',
                'closed_at' => null,
                'closed_by' => null,
            ]);
        });

        return back()->with('success', 'Payment completed.');
    }

    public function cancel(Request $request, Invoice $invoice)
    {
        $activePosSession = $this->activePosSession($request);

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first.');
        }

        abort_if($invoice->branch_id !== $activePosSession->branch_id, 403);
        abort_if($invoice->status === 'cancelled', 422, 'This invoice is already cancelled.');

        DB::transaction(function () use ($request, $invoice, $activePosSession) {
            $invoice->load(['payments.paymentMethod', 'lines']);

            foreach ($invoice->payments->where('status', '!=', 'cancelled') as $payment) {
                $this->reversePosSessionPaymentTotals($activePosSession, $payment);
                $payment->update([
                    'status' => 'cancelled',
                    'cancelled_by' => $request->user()->id,
                    'cancel_reason' => 'Invoice cancelled.',
                ]);
            }

            $wasStockSettled = ($invoice->stock_settlement_status ?? 'pending') === 'approved'
                && (float) $invoice->stock_settled_quantity > 0;

            $invoice->update([
                'status' => 'cancelled',
                'paid_amount' => 0,
                'balance_amount' => 0,
                'paid_at' => null,
                'cancelled_at' => now(),
                'cancelled_by' => $request->user()->id,
                'cancel_reason' => 'Cancelled from invoice management.',
                'stock_settlement_status' => $wasStockSettled ? 'pending' : ($invoice->stock_settlement_status ?? 'pending'),
                'stock_settlement_note' => $wasStockSettled
                    ? 'Invoice cancelled after stock settlement. Pending reversal.'
                    : $invoice->stock_settlement_note,
            ]);

            $invoice->lines()->update([
                'order_id' => null,
                'order_line_id' => null,
            ]);

            $invoice->diningSession?->update([
                'status' => 'open',
                'closed_at' => null,
                'closed_by' => null,
            ]);
        });

        return back()->with('success', 'Invoice cancelled. The order can be invoiced again or cancelled.');
    }

    public function cancelPayment(Request $request, Payment $payment)
    {
        $activePosSession = $this->activePosSession($request);

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first.');
        }

        $payment->load(['invoice.diningSession', 'paymentMethod']);

        abort_if($payment->branch_id !== $activePosSession->branch_id, 403);
        abort_if($payment->status === 'cancelled', 422, 'This receipt is already cancelled.');

        DB::transaction(function () use ($request, $payment, $activePosSession) {
            $this->reversePosSessionPaymentTotals($activePosSession, $payment);

            $payment->update([
                'status' => 'cancelled',
                'cancelled_by' => $request->user()->id,
                'cancel_reason' => 'Receipt cancelled from invoice management.',
            ]);

            $invoice = $payment->invoice;

            if ($invoice) {
                $this->syncInvoicePaymentStatus($invoice);
                $invoice->refresh();

                $invoice->diningSession?->update([
                    'status' => $invoice->status === 'paid' ? 'paid' : 'invoiced',
                    'closed_at' => null,
                    'closed_by' => null,
                ]);
            }
        });

        return back()->with('success', 'Receipt cancelled. The invoice is open for payment again.');
    }

    public function cancelInvoicePayments(Request $request, Invoice $invoice)
    {
        $activePosSession = $this->activePosSession($request);

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first.');
        }

        abort_if($invoice->branch_id !== $activePosSession->branch_id, 403);
        abort_if($invoice->status === 'cancelled', 422, 'Cancelled invoices cannot have receipts cancelled.');

        DB::transaction(function () use ($request, $invoice, $activePosSession) {
            $invoice->load(['diningSession', 'payments.paymentMethod']);
            $payments = $invoice->payments->where('status', '!=', 'cancelled');

            abort_if($payments->isEmpty(), 422, 'This invoice has no active receipts to cancel.');

            foreach ($payments as $payment) {
                $this->reversePosSessionPaymentTotals($activePosSession, $payment);

                $payment->update([
                    'status' => 'cancelled',
                    'cancelled_by' => $request->user()->id,
                    'cancel_reason' => 'All invoice receipts cancelled from invoice management.',
                ]);
            }

            $this->syncInvoicePaymentStatus($invoice);
            $invoice->refresh();

            $invoice->diningSession?->update([
                'status' => $invoice->status === 'paid' ? 'paid' : 'invoiced',
                'closed_at' => null,
                'closed_by' => null,
            ]);
        });

        return back()->with('success', 'All receipts for this invoice have been cancelled.');
    }

    private function activePosSession(Request $request): ?PosSession
    {
        return PosSession::where('opened_by', $request->user()->id)
            ->whereNull('closed_at')
            ->latest()
            ->first();
    }

    private function paymentMethodFor(string $method, string $currency, PosSession $posSession, ?int $paymentMethodId = null): ?PaymentMethod
    {
        if ($paymentMethodId) {
            $paymentMethod = PaymentMethod::query()
                ->where('id', $paymentMethodId)
                ->where('company_id', $posSession->company_id)
                ->where('currency', $currency)
                ->where('is_active', true)
                ->where(function ($query) use ($posSession) {
                    $query->whereNull('branch_id')
                        ->orWhere('branch_id', $posSession->branch_id);
                })
                ->first();

            if ($paymentMethod) {
                return $paymentMethod;
            }

            $paymentMethod = PaymentMethod::query()
                ->where('id', $paymentMethodId)
                ->where('company_id', $posSession->company_id)
                ->where('currency', $currency)
                ->where('is_active', true)
                ->first();

            if ($paymentMethod) {
                return $paymentMethod;
            }
        }

        $code = match ($method) {
            'cash-usd' => 'CASH_USD',
            'cash-khr' => 'CASH_KHR',
            'ebanking-usd' => 'EBANK_USD',
            'ebanking-khr' => 'EBANK_KHR',
            'ebank-usd' => 'EBANK_USD',
            'ebank-khr' => 'EBANK_KHR',
            default => null,
        };

        return PaymentMethod::query()
            ->where('company_id', $posSession->company_id)
            ->where('currency', $currency)
            ->where('is_active', true)
            ->when($code, fn ($query) => $query->where('code', $code))
            ->where(function ($query) use ($posSession) {
                $query->whereNull('branch_id')
                    ->orWhere('branch_id', $posSession->branch_id);
            })
            ->orderByRaw('case when branch_id = ? then 0 when branch_id is null then 1 else 2 end', [$posSession->branch_id])
            ->first()
            ?? PaymentMethod::query()
                ->where('company_id', $posSession->company_id)
                ->where('currency', $currency)
                ->where('is_active', true)
                ->when($code, fn ($query) => $query->where('code', $code))
                ->first();
    }

    private function formatInvoice(Invoice $invoice): array
    {
        $customer = $this->customerFor($invoice->customer_id);
        $posTerminal = $this->posTerminalFor($invoice->pos_terminal_id);
        $latestPayment = $invoice->payments
            ->where('status', '!=', 'cancelled')
            ->sortByDesc('id')
            ->first();
        $customerName = 'Walk-in Customer';
        $customerPhone = null;
        $posName = 'Terminal';

        if ($customer) {
            $customerName = $customer->name ?? $customer->customer_name ?? $customerName;
            $customerPhone = $customer->phone_number ?? $customer->phone ?? $customer->mobile;
        }

        if ($posTerminal) {
            $posName = $posTerminal->name;
        }

        return [
            'id' => $invoice->id,
            'dining_session_id' => $invoice->dining_session_id,
            'invoice_no' => $invoice->invoice_no,
            'status' => $invoice->status,
            'date' => $invoice->created_at?->toDateString(),
            'display_date' => $invoice->created_at?->format('M d, Y'),
            'pos_name' => $posName,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'seat_name' => $invoice->diningSession?->diningResource?->name,
            'subtotal' => (float) $invoice->subtotal,
            'discount_amount' => (float) $invoice->discount_amount,
            'tax_amount' => (float) $invoice->tax_amount,
            'grand_total' => (float) $invoice->grand_total,
            'paid_amount' => (float) $invoice->paid_amount,
            'balance_amount' => (float) $invoice->balance_amount,
            'exchange_rate' => (float) $invoice->exchange_rate_snapshot,
            'order_created_by' => $invoice->lines
                ->pluck('order.creator.name')
                ->filter()
                ->unique()
                ->values(),
            'invoice_created_by' => $invoice->issuer?->name ?? 'System',
            'receipt_created_by' => $latestPayment?->receiver?->name,
            'payments' => $invoice->payments
                ->sortByDesc('id')
                ->map(fn (Payment $payment) => [
                    'id' => $payment->id,
                    'payment_no' => $payment->payment_no,
                    'status' => $payment->status,
                    'method' => $payment->paymentMethod?->name,
                    'currency' => $payment->currency,
                    'amount_paid' => (float) $payment->amount_paid,
                    'received_amount' => (float) $payment->received_amount,
                    'amount_usd_equivalent' => (float) $payment->amount_usd_equivalent,
                    'change_usd_amount' => (float) $payment->change_usd_amount,
                    'change_khr_amount' => (float) $payment->change_khr_amount,
                    'paid_at' => $payment->paid_at?->format('Y-m-d H:i'),
                    'received_by' => $payment->receiver?->name,
                ])
                ->values(),
            'lines' => $invoice->lines->map(fn ($line) => [
                'id' => $line->id,
                'menu_name' => $line->menu_name_snapshot,
                'quantity' => (float) $line->quantity,
                'unit_price' => (float) $line->unit_price,
                'discount_amount' => (float) $line->discount_amount,
                'tax_amount' => (float) $line->tax_amount,
                'line_subtotal' => (float) $line->line_subtotal,
                'line_total' => (float) $line->line_total,
                'note' => $line->note,
            ]),
        ];
    }

    private function paymentSummary($invoices, PosSession $posSession): array
    {
        $summary = [
            'sales_usd' => 0,
            'sales_khr' => 0,
            'cash_usd' => 0,
            'cash_khr' => 0,
            'ebanking_usd' => 0,
            'ebanking_khr' => 0,
            'pay_later_usd' => 0,
            'pay_later_khr' => 0,
        ];

        foreach ($invoices as $invoice) {
            $exchangeRate = (float) ($invoice->exchange_rate_snapshot ?: 4100);
            $summary['sales_usd'] += (float) $invoice->grand_total;
            $summary['sales_khr'] += (float) $invoice->grand_total * $exchangeRate;

            if ($invoice->status === 'pay_later' || (float) $invoice->balance_amount > 0) {
                $summary['pay_later_usd'] += (float) $invoice->balance_amount;
                $summary['pay_later_khr'] += (float) $invoice->balance_amount * $exchangeRate;
            }

            foreach ($invoice->payments as $payment) {
                if ($payment->status === 'cancelled') {
                    continue;
                }

                $methodType = $payment->paymentMethod?->method_type;
                $methodCode = strtoupper((string) $payment->paymentMethod?->code);
                $currency = strtoupper((string) $payment->currency);

                if ($methodType === 'cash' || str_contains($methodCode, 'CASH')) {
                    if ($currency === 'KHR') {
                        $summary['cash_khr'] += (float) ($payment->received_amount ?? $payment->amount_paid);
                    } else {
                        $summary['cash_usd'] += (float) ($payment->received_amount ?? $payment->amount_paid);
                    }

                    $summary['cash_usd'] -= (float) ($payment->change_usd_amount ?? 0);
                    $summary['cash_khr'] -= (float) ($payment->change_khr_amount ?? 0);

                    continue;
                }

                if ($methodType === 'bank' || str_contains($methodCode, 'EBANK')) {
                    if ($currency === 'KHR') {
                        $summary['ebanking_khr'] += (float) $payment->amount_paid;
                    } else {
                        $summary['ebanking_usd'] += (float) $payment->amount_paid;
                    }
                }
            }
        }

        return [
            ...$summary,
            'expected_cash_usd' => (float) $posSession->opening_cash_usd + $summary['cash_usd'],
            'expected_cash_khr' => (float) $posSession->opening_cash_khr + $summary['cash_khr'],
        ];
    }

    private function paymentMethodsFor(PosSession $posSession): array
    {
        $paymentMethods = PaymentMethod::query()
            ->where('company_id', $posSession->company_id)
            ->where('is_active', true)
            ->whereIn('currency', ['USD', 'KHR'])
            ->whereIn('method_type', ['cash', 'bank'])
            ->where(function ($query) use ($posSession) {
                $query->whereNull('branch_id')
                    ->orWhere('branch_id', $posSession->branch_id);
            })
            ->orderByRaw("case method_type when 'cash' then 0 when 'bank' then 1 else 2 end")
            ->orderBy('currency')
            ->get();

        if ($paymentMethods->isEmpty()) {
            $paymentMethods = PaymentMethod::query()
                ->where('company_id', $posSession->company_id)
                ->where('is_active', true)
                ->whereIn('currency', ['USD', 'KHR'])
                ->whereIn('method_type', ['cash', 'bank'])
                ->orderByRaw("case method_type when 'cash' then 0 when 'bank' then 1 else 2 end")
                ->orderBy('currency')
                ->get();
        }

        return $paymentMethods
            ->map(fn (PaymentMethod $paymentMethod) => [
                'id' => $paymentMethod->id,
                'code' => $paymentMethod->code,
                'label' => $paymentMethod->name,
                'type' => $paymentMethod->method_type,
                'currency' => $paymentMethod->currency,
            ])
            ->values()
            ->all();
    }

    private function recordReceivedPaymentTotals(
        PosSession $posSession,
        string $method,
        string $currency,
        float $amountPaid,
        float $receivedAmount,
        float $changeUsdAmount,
        float $changeKhrAmount,
    ): void {
        if (str_starts_with($method, 'cash-')) {
            $cashUsdChange = $currency === 'USD' ? $receivedAmount : 0;
            $cashKhrChange = $currency === 'KHR' ? $receivedAmount : 0;

            $posSession->increment('total_cash_usd', $cashUsdChange - $changeUsdAmount);
            $posSession->increment('total_cash_khr', $cashKhrChange - $changeKhrAmount);

            return;
        }

        if (str_starts_with($method, 'ebanking-')) {
            if ($currency === 'USD') {
                $posSession->increment('total_ebanking_usd', $amountPaid);

                return;
            }

            $posSession->increment('total_ebanking_khr', $amountPaid);
        }
    }

    private function reversePosSessionPaymentTotals(PosSession $posSession, Payment $payment): void
    {
        $methodType = $payment->paymentMethod?->method_type;
        $methodCode = strtoupper((string) $payment->paymentMethod?->code);
        $currency = strtoupper((string) $payment->currency);

        if ($methodType === 'cash' || str_contains($methodCode, 'CASH')) {
            if ($currency === 'USD') {
                $posSession->decrement('total_cash_usd', (float) $payment->received_amount - (float) $payment->change_usd_amount);
                $posSession->increment('total_cash_khr', (float) $payment->change_khr_amount);

                return;
            }

            $posSession->decrement('total_cash_khr', (float) $payment->received_amount - (float) $payment->change_khr_amount);
            $posSession->increment('total_cash_usd', (float) $payment->change_usd_amount);

            return;
        }

        if ($methodType === 'bank' || str_contains($methodCode, 'EBANK')) {
            if ($currency === 'USD') {
                $posSession->decrement('total_ebanking_usd', (float) $payment->amount_paid);

                return;
            }

            $posSession->decrement('total_ebanking_khr', (float) $payment->amount_paid);
        }
    }

    private function syncInvoicePaymentStatus(Invoice $invoice): void
    {
        $paidUsd = (float) $invoice->payments()
            ->where('status', '!=', 'cancelled')
            ->sum('amount_usd_equivalent');
        $grandTotal = (float) $invoice->grand_total;
        $paidUsd = round(min($paidUsd, $grandTotal), 2);
        $balance = round(max(0, $grandTotal - $paidUsd), 2);

        $invoice->update([
            'status' => $balance <= 0.009
                ? 'paid'
                : ($paidUsd > 0 ? 'partially_paid' : 'pay_later'),
            'paid_amount' => $paidUsd,
            'balance_amount' => $balance,
            'paid_at' => $balance <= 0.009 ? now() : null,
        ]);
    }

    private function roundDownKhrChange(float $amount): float
    {
        if ($amount <= 0) {
            return 0;
        }

        return floor($amount / 100) * 100;
    }

    private function customerFor(?int $customerId): ?Customer
    {
        if (! $customerId) {
            return null;
        }

        return Customer::query()->find($customerId);
    }

    private function posTerminalFor(?int $posTerminalId): ?PosTerminal
    {
        if (! $posTerminalId) {
            return null;
        }

        return PosTerminal::query()->find($posTerminalId);
    }
}
