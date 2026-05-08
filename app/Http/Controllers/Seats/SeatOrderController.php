<?php

namespace App\Http\Controllers\Seats;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DiningSession;
use App\Models\ExchangeRate;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuPrice;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PosSession;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SeatOrderController extends Controller
{
    public function show(Request $request, DiningSession $diningSession)
    {
        $activePosSession = $this->activePosSession($request);

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first.');
        }

        if (! in_array($diningSession->status, ['open', 'invoiced', 'paid', 'pay_later'], true)) {
            return redirect()
                ->route('seats.index')
                ->with('error', 'This dining session is not open.');
        }

        $diningSession->load([
            'diningResource.diningResourceType',
            'customer',
            'orders.orderLines.menu',
        ]);

        $categoryId = $request->query('category_id');
        $search = $request->query('search');

        $menus = Menu::query()
            ->with(['menuCategory', 'defaultPrice'])
            ->where('company_id', $activePosSession->company_id)
            ->where(function ($q) use ($activePosSession) {
                $q->whereNull('branch_id')
                    ->orWhere('branch_id', $activePosSession->branch_id);
            })
            ->where('is_active', true)
            ->where('is_available', true)
            ->when($categoryId, fn ($q) => $q->where('menu_category_id', $categoryId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get()
            ->map(function ($menu) {
                $defaultPrice = $this->defaultMenuPriceFor($menu);
                $unitPrice = $menu->base_price ?? 0;

                if ($defaultPrice) {
                    $unitPrice = $defaultPrice->price;
                }

                return [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'code' => $menu->code,
                    'image' => $menu->image,
                    'category_name' => $menu->menuCategory?->name,
                    'menu_type' => $menu->menu_type,
                    'unit_price' => (float) $unitPrice,
                ];
            });

        $categories = MenuCategory::query()
            ->where('company_id', $activePosSession->company_id)
            ->where(function ($q) use ($activePosSession) {
                $q->whereNull('branch_id')
                    ->orWhere('branch_id', $activePosSession->branch_id);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $cartOrder = $this->getCartOrder($diningSession);
        $cartOrder->load(['orderLines.menu']);
        $customer = $this->customerFor($diningSession->customer_id);
        $customerName = 'Walk-in / General Customer';
        $customerPhone = null;

        if ($customer) {
            $customerName = $customer->name ?? $customer->customer_name ?? $customerName;
            $customerPhone = $customer->phone_number
                ?? $customer->phone
                ?? $customer->customer_phone
                ?? $customer->mobile;
        }

        $latestInvoice = $diningSession->invoices()
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->first(['invoice_no', 'status', 'grand_total']);

        return Inertia::render('Seats/Orders', [
            'posSession' => [
                'id' => $activePosSession->id,
                'session_no' => $activePosSession->session_no ?? $activePosSession->session_number ?? ('POS-'.$activePosSession->id),
            ],
            'diningSession' => [
                'id' => $diningSession->id,
                'session_no' => $diningSession->session_no,
                'status' => $diningSession->status,
                'invoice_no' => $latestInvoice?->invoice_no,
                'invoice_status' => $latestInvoice?->status,
                'invoice_total' => $latestInvoice ? (float) $latestInvoice->grand_total : null,
                'seat_name' => $diningSession->diningResource?->name,
                'seat_type' => $diningSession->diningResource?->diningResourceType?->name,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
            ],
            'menus' => $menus,
            'categories' => $categories,
            'cart' => $this->formatOrder($cartOrder),
            'exchangeRate' => $this->latestExchangeRate($activePosSession),
            'paymentMethods' => $this->paymentMethodsFor($activePosSession),
            'historyOrders' => $diningSession->orders()
                ->with(['orderLines.menu'])
                ->where('id', '!=', $cartOrder->id)
                ->latest()
                ->get()
                ->map(fn ($order) => $this->formatOrder($order)),
            'invoices' => $diningSession->invoices()
                ->with(['lines'])
                ->where('status', '!=', 'cancelled')
                ->latest()
                ->get()
                ->map(fn ($invoice) => $this->formatInvoice($invoice)),
            'filters' => [
                'category_id' => $categoryId,
                'search' => $search,
            ],
        ]);
    }

    public function addItem(Request $request, DiningSession $diningSession)
    {
        $data = $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'qty' => ['nullable', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $menu = Menu::with('defaultPrice')->findOrFail($data['menu_id']);

        DB::transaction(function () use ($diningSession, $menu, $data) {
            $qty = (float) ($data['qty'] ?? 1);
            $defaultPrice = $this->defaultMenuPriceFor($menu);
            $unitPrice = (float) ($menu->base_price ?? 0);

            if ($defaultPrice) {
                $unitPrice = (float) $defaultPrice->price;
            }

            $taxRate = 10;

            $order = $this->getCartOrder($diningSession);

            $line = OrderLine::where('order_id', $order->id)
                ->where('menu_id', $menu->id)
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($line) {
                $line->quantity = (float) $line->quantity + $qty;
                $line->note = $data['note'] ?? $line->note;
                $this->calculateLine($line);
                $line->save();

                return;
            }

            $line = new OrderLine([
                'order_id' => $order->id,
                'menu_id' => $menu->id,
                'menu_name_snapshot' => $menu->name,
                'menu_type_snapshot' => $menu->menu_type,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount_amount' => 0,
                'tax_id' => $menu->tax_id,
                'tax_rate_snapshot' => $taxRate,
                'note' => $data['note'] ?? null,
                'status' => 'ordered',
            ]);

            $this->calculateLine($line);
            $line->save();
        });

        return back();
    }

    public function updateItem(Request $request, DiningSession $diningSession, OrderLine $orderLine)
    {
        $data = $request->validate([
            'qty' => ['required', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->ensureLineBelongsToSession($diningSession, $orderLine);

        DB::transaction(function () use ($orderLine, $data) {
            $orderLine->quantity = (float) $data['qty'];
            if (array_key_exists('note', $data)) {
                $orderLine->note = $data['note'] ?? null;
            }
            $this->calculateLine($orderLine);
            $orderLine->save();
        });

        return back();
    }

    public function removeItem(DiningSession $diningSession, OrderLine $orderLine)
    {
        $this->ensureLineBelongsToSession($diningSession, $orderLine);

        $orderLine->delete();

        return back();
    }

    public function clearItems(DiningSession $diningSession)
    {
        $order = $this->getCartOrder($diningSession);

        $order->orderLines()->delete();

        return back();
    }

    public function sendToKitchen(Request $request, DiningSession $diningSession)
    {
        $data = $request->validate([
            'lines' => ['nullable', 'array'],
            'lines.*.menu_id' => ['required_with:lines', 'exists:menus,id'],
            'lines.*.qty' => ['required_with:lines', 'numeric', 'min:1'],
            'lines.*.note' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($diningSession, $data) {
            $order = $this->getCartOrder($diningSession);

            if (isset($data['lines']) && is_array($data['lines'])) {
                $this->replaceDraftOrderLines($order, $data['lines']);
            }

            $order->load('orderLines');

            abort_if(
                $order->orderLines->isEmpty(),
                422,
                'Please add menu items before sending to kitchen.'
            );

            $order->update([
                'status' => 'sent_to_kitchen',
                'sent_to_kitchen_at' => now(),
            ]);
        });

        return back()->with('success', 'Order sent to kitchen.');
    }

    public function settle(Request $request, DiningSession $diningSession)
    {
        $data = $request->validate([
            'method' => ['required', 'string', 'max:50'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'currency' => ['required', 'in:USD,KHR'],
            'received_amount' => ['nullable', 'numeric', 'min:0'],
            'operation_status' => ['required', 'in:invoice,invoice_receipt_done'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'change_usd_amount' => ['nullable', 'numeric', 'min:0'],
            'change_khr_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $activePosSession = $this->activePosSession($request);

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first.');
        }

        DB::transaction(function () use ($diningSession, $activePosSession, $data, $request) {
            $orders = $diningSession->orders()
                ->with('orderLines')
                ->whereNotIn('status', ['draft', 'cancelled'])
                ->whereDoesntHave('orderLines.invoiceLines')
                ->get();

            $orderLines = $orders->flatMap->orderLines;

            if ($orderLines->isEmpty()) {
                abort(422, 'Please confirm at least one order before checking bill.');
            }

            $subtotal = (float) $orderLines->sum('line_subtotal');
            $taxAmount = (float) $orderLines->sum('tax_amount');
            $discountAmount = min($subtotal, (float) ($data['discount_amount'] ?? 0));
            $grandTotal = max(0, $subtotal - $discountAmount) + $taxAmount;
            $isPayLater = $data['operation_status'] === 'invoice';
            $invoiceStatus = $isPayLater ? 'pay_later' : 'paid';
            $paidAmount = $isPayLater ? 0 : $grandTotal;
            $exchangeRate = $this->latestExchangeRate($activePosSession);

            $invoice = new Invoice([
                'company_id' => $diningSession->company_id,
                'branch_id' => $diningSession->branch_id,
                'pos_terminal_id' => $diningSession->pos_terminal_id,
                'dining_session_id' => $diningSession->id,
                'customer_id' => $diningSession->customer_id,
                'invoice_no' => DocumentNumber::make(Invoice::class, 'invoice_no', 'IN'),
                'currency' => 'USD',
                'exchange_rate_snapshot' => $exchangeRate,
                'issued_at' => now(),
                'issued_by' => $request->user()->id,
            ]);

            $invoice->fill([
                'status' => $invoiceStatus,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'balance_amount' => $grandTotal - $paidAmount,
                'paid_at' => $isPayLater ? null : now(),
            ])->save();

            foreach ($orders as $order) {
                foreach ($order->orderLines as $line) {
                    InvoiceLine::create([
                        'invoice_id' => $invoice->id,
                        'order_id' => $order->id,
                        'order_line_id' => $line->id,
                        'menu_id' => $line->menu_id,
                        'menu_name_snapshot' => $line->menu_name_snapshot,
                        'menu_type_snapshot' => $line->menu_type_snapshot,
                        'quantity' => $line->quantity,
                        'unit_price' => $line->unit_price,
                        'discount_amount' => $line->discount_amount,
                        'tax_id' => $line->tax_id,
                        'tax_rate_snapshot' => $line->tax_rate_snapshot,
                        'tax_amount' => $line->tax_amount,
                        'line_subtotal' => $line->line_subtotal,
                        'line_total' => $line->line_total,
                        'note' => $line->note,
                    ]);
                }
            }

            if (! $isPayLater) {
                $method = $this->paymentMethodFor(
                    $data['method'],
                    $data['currency'],
                    $activePosSession,
                    $data['payment_method_id'] ?? null,
                );

                abort_if(! $method, 422, 'Please select a valid payment method.');

                $isCash = str_starts_with($data['method'], 'cash-');
                $amountPaid = $data['currency'] === 'KHR' ? $grandTotal * $exchangeRate : $grandTotal;
                $receivedAmount = $isCash ? (float) ($data['received_amount'] ?? $amountPaid) : $amountPaid;
                $changeUsdAmount = $isCash ? (float) ($data['change_usd_amount'] ?? 0) : 0;
                $changeKhrAmount = $isCash ? $this->roundKhrChange((float) ($data['change_khr_amount'] ?? 0)) : 0;

                if ($isCash) {
                    abort_if(
                        $receivedAmount < $amountPaid,
                        422,
                        'Received cash must be enough to cover the invoice total.'
                    );

                    $overpaidUsd = $data['currency'] === 'USD'
                        ? $receivedAmount - $amountPaid
                        : ($receivedAmount - $amountPaid) / $exchangeRate;
                    $changeUsdEquivalent = $changeUsdAmount + ($changeKhrAmount / $exchangeRate);
                    $roundingToleranceUsd = 99 / $exchangeRate;

                    abort_if(
                        $changeUsdEquivalent > $overpaidUsd + $roundingToleranceUsd + 0.01,
                        422,
                        'Change amount cannot be greater than the overpaid amount.'
                    );
                }

                Payment::create([
                    'company_id' => $diningSession->company_id,
                    'branch_id' => $diningSession->branch_id,
                    'invoice_id' => $invoice->id,
                    'payment_method_id' => $method?->id,
                    'payment_no' => DocumentNumber::make(Payment::class, 'payment_no', 'PY'),
                    'status' => 'paid',
                    'currency' => $data['currency'],
                    'amount_paid' => $amountPaid,
                    'received_amount' => $receivedAmount,
                    'change_usd_amount' => $changeUsdAmount,
                    'change_khr_amount' => $changeKhrAmount,
                    'exchange_rate_snapshot' => $exchangeRate,
                    'amount_usd_equivalent' => $grandTotal,
                    'amount_khr_equivalent' => $grandTotal * $exchangeRate,
                    'paid_at' => now(),
                    'received_by' => $request->user()->id,
                ]);

                $this->recordPosSessionPaymentTotals(
                    $activePosSession,
                    $data['method'],
                    $data['currency'],
                    $grandTotal,
                    $amountPaid,
                    $receivedAmount,
                    $changeUsdAmount,
                    $changeKhrAmount,
                    $exchangeRate,
                );
            } else {
                $activePosSession->increment('total_pay_later_usd', $grandTotal);
                $activePosSession->increment('total_pay_later_khr', $grandTotal * $exchangeRate);
            }

            $diningSession->update([
                'status' => $isPayLater ? 'invoiced' : 'paid',
                'closed_at' => null,
                'closed_by' => null,
            ]);
        });

        return back()->with('success', $data['operation_status'] === 'invoice' ? 'Invoice created.' : 'Payment completed.');
    }

    public function updateCustomer(Request $request, DiningSession $diningSession)
    {
        $activePosSession = $this->activePosSession($request);

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first.');
        }

        $data = $request->validate([
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_name' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($diningSession, $activePosSession, $data) {
            $phoneNumber = trim((string) ($data['customer_phone'] ?? ''));
            $customerName = trim((string) ($data['customer_name'] ?? ''));
            $customerId = null;

            if ($phoneNumber !== '') {
                $customer = Customer::updateOrCreate(
                    [
                        'company_id' => $activePosSession->company_id,
                        'phone_number' => $phoneNumber,
                    ],
                    [
                        'branch_id' => $activePosSession->branch_id,
                        'name' => $customerName !== '' ? $customerName : null,
                        'is_active' => true,
                    ]
                );

                $customerId = $customer->id;
            }

            $diningSession->update(['customer_id' => $customerId]);

            $diningSession->invoices()
                ->where('status', '!=', 'cancelled')
                ->update(['customer_id' => $customerId]);
        });

        return back()->with('success', 'Customer updated.');
    }

    public function closeOrder(Request $request, DiningSession $diningSession)
    {
        abort_if(
            ! in_array($diningSession->status, ['invoiced', 'paid', 'pay_later'], true),
            422,
            'Please create an invoice or receive payment before closing this order.'
        );

        abort_if(
            $this->hasUnbilledConfirmedOrders($diningSession),
            422,
            'Please check bill for confirmed orders before closing this order.'
        );

        DB::transaction(function () use ($diningSession, $request) {
            $diningSession->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => $request->user()->id,
            ]);

            $diningSession->diningResource()->update([
                'status' => 'available',
            ]);
        });

        return redirect()
            ->route('seats.index')
            ->with('success', 'Order closed and dining resource is available.');
    }

    private function activePosSession(Request $request): ?PosSession
    {
        return PosSession::where('opened_by', $request->user()->id)
            ->whereNull('closed_at')
            ->latest()
            ->first();
    }

    private function getCartOrder(DiningSession $diningSession): Order
    {
        return Order::firstOrCreate(
            [
                'dining_session_id' => $diningSession->id,
                'status' => 'draft',
            ],
            [
                'company_id' => $diningSession->company_id,
                'branch_id' => $diningSession->branch_id,
                'order_no' => $this->makeDiningSessionOrderNumber($diningSession),
                'created_by' => auth()->id(),
            ]
        );
    }

    private function makeDiningSessionOrderNumber(DiningSession $diningSession): string
    {
        $prefix = $diningSession->session_no.'-OR';

        $lastOrderNo = Order::query()
            ->where('dining_session_id', $diningSession->id)
            ->where('order_no', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('order_no')
            ->value('order_no');

        $sequence = 1;

        if (is_string($lastOrderNo) && str_starts_with($lastOrderNo, $prefix)) {
            $sequence = ((int) substr($lastOrderNo, strlen($prefix))) + 1;
        }

        return $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    private function calculateLine(OrderLine $line): void
    {
        $subtotal = ((float) $line->quantity * (float) $line->unit_price) - (float) $line->discount_amount;
        $taxAmount = $subtotal * ((float) $line->tax_rate_snapshot / 100);

        $line->line_subtotal = $subtotal;
        $line->tax_amount = $taxAmount;
        $line->line_total = $subtotal + $taxAmount;
    }

    /**
     * @param  array<int, array{menu_id: int, qty: numeric-string|int|float, note?: string|null}>  $lines
     */
    private function replaceDraftOrderLines(Order $order, array $lines): void
    {
        $order->orderLines()->delete();

        $menuIds = collect($lines)
            ->pluck('menu_id')
            ->unique()
            ->values();

        $menus = Menu::query()
            ->with('defaultPrice')
            ->whereIn('id', $menuIds)
            ->get()
            ->keyBy('id');

        collect($lines)
            ->groupBy('menu_id')
            ->each(function ($menuLines, $menuId) use ($order, $menus) {
                $menu = $menus->get((int) $menuId);

                if (! $menu) {
                    return;
                }

                $qty = (float) $menuLines->sum(fn ($line) => $line['qty']);
                $note = $menuLines
                    ->pluck('note')
                    ->map(fn ($note) => trim((string) $note))
                    ->filter()
                    ->last();
                $defaultPrice = $this->defaultMenuPriceFor($menu);
                $unitPrice = (float) ($menu->base_price ?? 0);

                if ($defaultPrice) {
                    $unitPrice = (float) $defaultPrice->price;
                }

                $line = new OrderLine([
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                    'menu_name_snapshot' => $menu->name,
                    'menu_type_snapshot' => $menu->menu_type,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0,
                    'tax_id' => $menu->tax_id,
                    'tax_rate_snapshot' => 10,
                    'note' => $note ?: null,
                    'status' => 'ordered',
                ]);

                $this->calculateLine($line);
                $line->save();
            });
    }

    private function ensureLineBelongsToSession(DiningSession $diningSession, OrderLine $orderLine): void
    {
        $orderLine->loadMissing('order');

        abort_if(
            ! $orderLine->order || $orderLine->order->dining_session_id !== $diningSession->id,
            403,
            'This order line does not belong to this dining session.'
        );
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
        }

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
            ->orderByRaw('case when branch_id = ? then 0 when branch_id is null then 1 else 2 end', [$posSession->branch_id])
            ->first()
            ?? PaymentMethod::query()
                ->where('company_id', $posSession->company_id)
                ->where('currency', $currency)
                ->where('is_active', true)
                ->when($code, fn ($query) => $query->where('code', $code))
                ->first();
    }

    private function latestExchangeRate(PosSession $posSession): float
    {
        return (float) (ExchangeRate::query()
            ->where('company_id', $posSession->company_id)
            ->where('from_currency', 'USD')
            ->where('to_currency', 'KHR')
            ->where('is_active', true)
            ->where('effective_date', '<=', now()->toDateString())
            ->latest('effective_date')
            ->value('rate') ?: 4100);
    }

    private function roundKhrChange(float $amount): float
    {
        if ($amount <= 0) {
            return 0;
        }

        return ceil($amount / 100) * 100;
    }

    private function paymentMethodsFor(PosSession $posSession): array
    {
        $query = PaymentMethod::query()
            ->where('company_id', $posSession->company_id)
            ->where('is_active', true)
            ->whereIn('currency', ['USD', 'KHR'])
            ->whereIn('method_type', ['cash', 'bank'])
            ->where(function ($query) use ($posSession) {
                $query->whereNull('branch_id')
                    ->orWhere('branch_id', $posSession->branch_id);
            });

        $paymentMethods = (clone $query)
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

    private function recordPosSessionPaymentTotals(
        PosSession $posSession,
        string $method,
        string $currency,
        float $grandTotal,
        float $amountPaid,
        float $receivedAmount,
        float $changeUsdAmount,
        float $changeKhrAmount,
        float $exchangeRate
    ): void {
        $posSession->increment('total_sales_usd', $grandTotal);
        $posSession->increment('total_sales_khr', $grandTotal * $exchangeRate);

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

    private function hasUnbilledConfirmedOrders(DiningSession $diningSession): bool
    {
        return $diningSession->orders()
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereDoesntHave('orderLines.invoiceLines')
            ->exists();
    }

    private function formatOrder(Order $order): array
    {
        $lines = $order->orderLines;
        $invoice = Invoice::query()
            ->whereHas('lines', fn ($query) => $query->where('order_id', $order->id))
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->first(['id', 'invoice_no', 'status']);

        return [
            'id' => $order->id,
            'order_no' => $order->order_no,
            'status' => $order->status,
            'invoice_no' => $invoice?->invoice_no,
            'invoice_status' => $invoice?->status,
            'subtotal' => (float) $lines->sum('line_subtotal'),
            'tax_amount' => (float) $lines->sum('tax_amount'),
            'total_amount' => (float) $lines->sum('line_total'),
            'created_at' => $order->created_at?->format('Y-m-d H:i'),
            'lines' => $lines->map(fn ($line) => [
                'id' => $line->id,
                'menu_id' => $line->menu_id,
                'menu_name' => $line->menu_name_snapshot,
                'qty' => (float) $line->quantity,
                'unit_price' => (float) $line->unit_price,
                'subtotal' => (float) $line->line_subtotal,
                'tax_amount' => (float) $line->tax_amount,
                'total_amount' => (float) $line->line_total,
                'note' => $line->note,
                'status' => $line->status,
            ]),
        ];
    }

    private function formatInvoice(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'invoice_no' => $invoice->invoice_no,
            'status' => $invoice->status,
            'subtotal' => (float) $invoice->subtotal,
            'discount_amount' => (float) $invoice->discount_amount,
            'tax_amount' => (float) $invoice->tax_amount,
            'grand_total' => (float) $invoice->grand_total,
            'paid_amount' => (float) $invoice->paid_amount,
            'balance_amount' => (float) $invoice->balance_amount,
            'created_at' => $invoice->created_at?->format('Y-m-d H:i'),
            'lines' => $invoice->lines->map(fn ($line) => [
                'id' => $line->id,
                'menu_name' => $line->menu_name_snapshot,
                'qty' => (float) $line->quantity,
                'total_amount' => (float) $line->line_total,
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

    private function defaultMenuPriceFor(Menu $menu): ?MenuPrice
    {
        return MenuPrice::query()
            ->where('menu_id', $menu->id)
            ->where('is_default', true)
            ->first();
    }
}
