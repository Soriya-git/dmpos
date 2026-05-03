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

        $invoices = Invoice::query()
            ->with([
                'customer',
                'lines',
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
            ->get()
            ->map(fn (Invoice $invoice) => $this->formatInvoice($invoice));

        return Inertia::render('Sales/Index', [
            'posSession' => [
                'id' => $activePosSession->id,
                'session_no' => $activePosSession->session_no ?? $activePosSession->session_number ?? ('POS-'.$activePosSession->id),
            ],
            'invoices' => $invoices,
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
            'currency' => ['required', 'in:USD,KHR'],
            'received_amount' => ['nullable', 'numeric', 'min:0'],
            'operation_status' => ['required', 'in:invoice_receipt_done'],
        ]);

        $activePosSession = $this->activePosSession($request);

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first.');
        }

        abort_if($invoice->branch_id !== $activePosSession->branch_id, 403);
        abort_if(in_array($invoice->status, ['paid', 'cancelled'], true), 422, 'This invoice cannot receive payment.');
        abort_if((float) $invoice->balance_amount <= 0, 422, 'This invoice has no balance to receive.');

        DB::transaction(function () use ($invoice, $activePosSession, $data, $request) {
            $balanceUsd = (float) $invoice->balance_amount;
            $exchangeRate = (float) ($invoice->exchange_rate_snapshot ?: 4100);
            $amountPaid = $data['currency'] === 'KHR' ? $balanceUsd * $exchangeRate : $balanceUsd;
            $method = $this->paymentMethodFor($data['method'], $data['currency'], $activePosSession);

            Payment::create([
                'company_id' => $invoice->company_id,
                'branch_id' => $invoice->branch_id,
                'invoice_id' => $invoice->id,
                'payment_method_id' => $method?->id,
                'payment_no' => DocumentNumber::make(Payment::class, 'payment_no', 'PY'),
                'status' => 'paid',
                'currency' => $data['currency'],
                'amount_paid' => $amountPaid,
                'exchange_rate_snapshot' => $exchangeRate,
                'amount_usd_equivalent' => $balanceUsd,
                'amount_khr_equivalent' => $balanceUsd * $exchangeRate,
                'paid_at' => now(),
                'received_by' => $request->user()->id,
            ]);

            $invoice->update([
                'status' => 'paid',
                'paid_amount' => (float) $invoice->grand_total,
                'balance_amount' => 0,
                'paid_at' => now(),
            ]);

            $invoice->diningSession?->update([
                'status' => 'paid',
                'closed_at' => null,
                'closed_by' => null,
            ]);
        });

        return back()->with('success', 'Payment completed.');
    }

    private function activePosSession(Request $request): ?PosSession
    {
        return PosSession::where('opened_by', $request->user()->id)
            ->whereNull('closed_at')
            ->latest()
            ->first();
    }

    private function paymentMethodFor(string $method, string $currency, PosSession $posSession): ?PaymentMethod
    {
        $code = match ($method) {
            'cash-usd' => 'CASH_USD',
            'cash-khr' => 'CASH_KHR',
            'ebanking-usd' => 'EBANK_USD',
            'ebanking-khr' => 'EBANK_KHR',
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
            ->first();
    }

    private function formatInvoice(Invoice $invoice): array
    {
        $customer = $this->customerFor($invoice->customer_id);
        $posTerminal = $this->posTerminalFor($invoice->pos_terminal_id);
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
