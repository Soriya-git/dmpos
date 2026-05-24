<?php

namespace App\Http\Controllers\Seats;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\DiningResource;
use App\Models\DiningSession;
use App\Models\ExchangeRate;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\MembershipCard;
use App\Models\MembershipCardBalance;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuPrice;
use App\Models\MenuPriceList;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PosSession;
use App\Models\Printer;
use App\Models\PrintJob;
use App\Models\PrintTemplate;
use App\Services\MembershipCardLedger;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            'menuPriceList',
            'orders.orderLines.menu',
        ]);

        $categoryId = $request->query('category_id');
        $search = $request->query('search');

        $menus = Menu::query()
            ->with(['menuCategory', 'prices'])
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
            ->map(function ($menu) use ($diningSession, $activePosSession) {
                $unitPrice = $this->menuPriceFor(
                    $menu,
                    $diningSession->menu_price_list_id,
                    $activePosSession->branch_id
                );

                return [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'code' => $menu->code,
                    'image' => $menu->image,
                    'category_name' => $menu->menuCategory?->name,
                    'menu_type' => $menu->menu_type,
                    'unit_price' => $unitPrice,
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
        $cartOrder->load(['orderLines.menu', 'orderLines.invoiceLines']);
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
                'customer_id' => $diningSession->customer_id,
                'status' => $diningSession->status,
                'invoice_no' => $latestInvoice?->invoice_no,
                'invoice_status' => $latestInvoice?->status,
                'invoice_total' => $latestInvoice ? (float) $latestInvoice->grand_total : null,
                'seat_name' => $diningSession->diningResource?->name,
                'seat_type' => $diningSession->diningResource?->diningResourceType?->name,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'price_list_name' => $diningSession->menuPriceList?->name,
            ],
            'menus' => $menus,
            'categories' => $categories,
            'cart' => $this->formatOrder($cartOrder),
            'exchangeRate' => $this->latestExchangeRate($activePosSession),
            'paymentMethods' => $this->paymentMethodsFor($activePosSession),
            'membershipCards' => $this->membershipCardsForCustomer($diningSession->customer_id, $activePosSession),
            'customers' => $this->customerOptions($activePosSession),
            'historyOrders' => $diningSession->orders()
                ->with(['orderLines.menu', 'orderLines.invoiceLines'])
                ->where('id', '!=', $cartOrder->id)
                ->latest()
                ->get()
                ->map(fn ($order) => $this->formatOrder($order)),
            'printOrders' => $this->printOrdersFor($diningSession),
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

        $menu = Menu::with('prices')->findOrFail($data['menu_id']);

        DB::transaction(function () use ($diningSession, $menu, $data) {
            $qty = (float) ($data['qty'] ?? 1);
            $unitPrice = $this->menuPriceFor($menu, $diningSession->menu_price_list_id, $diningSession->branch_id);

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
                'bill_group' => 'Bill A',
                'assigned_dining_resource_id' => $diningSession->dining_resource_id,
                'note' => $data['note'] ?? null,
                'status' => 'ordered',
            ]);

            $this->calculateLine($line);
            $line->save();
        });

        return back();
    }

    public function manage(Request $request, DiningSession $diningSession)
    {
        $activePosSession = $this->activePosSession($request);

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first.');
        }

        $diningSession->load(['diningResource', 'orders.orderLines']);

        $lines = $diningSession->orders()
            ->with(['orderLines' => fn ($query) => $query
                ->where('status', '!=', 'cancelled')
                ->whereDoesntHave('invoiceLines')
                ->orderBy('id')])
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->latest()
            ->get()
            ->flatMap(fn (Order $order) => $order->orderLines->map(fn (OrderLine $line): array => [
                'id' => $line->id,
                'orderId' => $order->id,
                'orderNo' => $order->order_no,
                'menuName' => $line->menu_name_snapshot,
                'quantity' => (float) $line->quantity,
                'unitPrice' => (float) $line->unit_price,
                'totalAmount' => (float) $line->line_total,
                'note' => $line->note,
                'status' => $line->status,
                'billGroup' => $line->bill_group ?: 'Bill A',
                'diningResourceId' => $line->assigned_dining_resource_id ?: $diningSession->dining_resource_id,
            ]))
            ->values();

        $resources = DiningResource::query()
            ->where('branch_id', $activePosSession->branch_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'status'])
            ->map(fn (DiningResource $resource): array => [
                'id' => $resource->id,
                'name' => $resource->name,
                'code' => $resource->code,
                'status' => $resource->status,
            ]);

        return Inertia::render('Seats/ManageOrders', [
            'diningSession' => [
                'id' => $diningSession->id,
                'sessionNo' => $diningSession->session_no,
                'seatName' => $diningSession->diningResource?->name,
                'diningResourceId' => $diningSession->dining_resource_id,
            ],
            'lines' => $lines,
            'resources' => $resources,
        ]);
    }

    public function saveManage(Request $request, DiningSession $diningSession)
    {
        $activePosSession = $this->activePosSession($request);

        abort_if(! $activePosSession, 422, 'Please open POS session first.');
        abort_if($diningSession->invoices()->where('status', '!=', 'cancelled')->exists(), 422, 'Orders cannot be managed after check bill.');

        $data = $request->validate([
            'assignments' => ['required', 'array', 'min:1'],
            'assignments.*.line_id' => ['required', 'integer', 'exists:order_lines,id'],
            'assignments.*.bill_group' => ['required', 'string', 'max:50'],
            'assignments.*.dining_resource_id' => ['required', 'integer', 'exists:dining_resources,id'],
        ]);

        $sourceSessionClosed = DB::transaction(function () use ($data, $diningSession, $activePosSession, $request): bool {
            $resources = DiningResource::query()
                ->where('branch_id', $activePosSession->branch_id)
                ->whereIn('id', collect($data['assignments'])->pluck('dining_resource_id')->unique())
                ->get()
                ->keyBy('id');

            foreach ($data['assignments'] as $assignment) {
                $line = OrderLine::query()
                    ->with(['order', 'invoiceLines'])
                    ->lockForUpdate()
                    ->findOrFail($assignment['line_id']);

                abort_if(
                    ! $line->order || (int) $line->order->dining_session_id !== (int) $diningSession->id,
                    403,
                    'This order line does not belong to this dining session.'
                );
                abort_if($line->invoiceLines->isNotEmpty(), 422, 'Checked bill lines cannot be moved.');
                abort_if($line->status === 'cancelled', 422, 'Cancelled lines cannot be moved.');

                $targetResourceId = (int) $assignment['dining_resource_id'];
                $targetResource = $resources->get($targetResourceId);

                abort_if(! $targetResource, 422, 'Selected table is not available for this branch.');

                $targetSession = $targetResourceId === (int) $diningSession->dining_resource_id
                    ? $diningSession
                    : $this->openSessionForResource($targetResource, $diningSession, $activePosSession, $request);
                $targetOrder = $this->openManagedOrderForSession($targetSession, $line->order);

                $line->update([
                    'order_id' => $targetOrder->id,
                    'bill_group' => trim((string) $assignment['bill_group']) ?: 'Bill A',
                    'assigned_dining_resource_id' => $targetResourceId,
                ]);
            }

            return $this->syncResourceStatusesAfterManage($diningSession);
        });

        if ($sourceSessionClosed) {
            return redirect()
                ->route('seats.index')
                ->with('success', 'Order assignments have been saved and the original table is now available.');
        }

        return redirect()
            ->route('seats.orders.show', $diningSession)
            ->with('success', 'Order assignments have been saved.');
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

        DB::transaction(function () use ($diningSession, $data, $request) {
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

            $order->load(['orderLines.menu.printer', 'diningSession.diningResource']);
            $this->createPrintJobsForOrder($order, $request);
        });

        return back()->with('success', 'Order sent to kitchen.');
    }

    public function reprint(Request $request, DiningSession $diningSession, PrintJob $printJob)
    {
        $orderIds = $diningSession->orders()->pluck('id');

        abort_if(
            $printJob->reference_type !== 'order' || ! $orderIds->contains((int) $printJob->reference_id),
            403,
            'This print job does not belong to this dining session.'
        );

        $payload = $printJob->payload ?? [];
        $payload['is_reprint'] = true;
        $payload['reprint_of'] = $printJob->job_no;
        $payload['reprinted_at'] = now()->toDateTimeString();
        $payload['reprinted_by'] = $request->user()?->name;

        PrintJob::create([
            'company_id' => $printJob->company_id,
            'branch_id' => $printJob->branch_id,
            'printer_id' => $printJob->printer_id,
            'print_template_id' => $printJob->print_template_id,
            'job_no' => DocumentNumber::make(PrintJob::class, 'job_no', 'PJ'),
            'job_type' => $printJob->job_type,
            'status' => 'pending',
            'reference_type' => $printJob->reference_type,
            'reference_id' => $printJob->reference_id,
            'reference_no' => $printJob->reference_no,
            'payload' => $payload,
        ]);

        return back()->with('success', 'Re-print job has been queued.');
    }

    public function previewPrintJob(DiningSession $diningSession, PrintJob $printJob)
    {
        $diningSession->loadMissing('branch');
        $orderIds = $diningSession->orders()->pluck('id');

        abort_if(
            $printJob->reference_type !== 'order' || ! $orderIds->contains((int) $printJob->reference_id),
            403,
            'This print job does not belong to this dining session.'
        );

        $payload = $printJob->payload ?? [];
        $jobType = (string) $printJob->job_type;
        $title = match ($jobType) {
            'stock_ticket' => 'Stock Slip',
            'bar_ticket' => 'Bar Slip',
            'cancel_slip' => data_get($payload, 'is_return_slip') ? 'Return Slip' : 'Cancel Slip',
            default => 'Kitchen Slip',
        };

        return view('prints.pos-document', [
            'document' => [
                'type' => 'slip',
                'title' => $title,
                'subtitle' => strtoupper(str_replace('_', ' ', $jobType)),
                'status' => $printJob->status,
                'no' => $printJob->reference_no,
                'jobNo' => $printJob->job_no,
                'printerName' => data_get($payload, 'printer.name', $printJob->printer?->name ?? 'Browser Preview'),
                'branch' => $this->documentBranch($diningSession->branch),
                'seat' => data_get($payload, 'order.seat'),
                'date' => $printJob->created_at?->format('Y-m-d H:i'),
                'staff' => data_get($payload, 'order.created_by') ?? data_get($payload, 'cancelled_by'),
                'isReprint' => (bool) data_get($payload, 'is_reprint', false),
                'isCancel' => (bool) data_get($payload, 'is_cancel_slip', false)
                    || (bool) data_get($payload, 'is_return_slip', false)
                    || $jobType === 'cancel_slip',
                'lines' => collect(data_get($payload, 'lines', []))
                    ->map(fn (array $line): array => [
                        'name' => (string) ($line['name'] ?? 'Menu item'),
                        'quantity' => (float) ($line['quantity'] ?? 0),
                        'note' => $line['note'] ?? null,
                        'status' => $line['status'] ?? null,
                    ])
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function previewCurrentInvoice(Request $request, DiningSession $diningSession)
    {
        $diningSession->loadMissing(['branch', 'diningResource']);
        $billGroup = trim((string) $request->query('bill_group', ''));
        $orders = $diningSession->orders()
            ->with(['orderLines' => fn ($query) => $query
                ->whereDoesntHave('invoiceLines')
                ->when($billGroup !== '', fn ($lineQuery) => $this->applyBillGroupFilter($lineQuery, $billGroup))])
            ->where('status', '!=', 'draft')
            ->get();
        $lines = $orders->flatMap->orderLines;

        abort_if($lines->isEmpty(), 422, 'Please confirm at least one order before printing invoice.');

        $payableLines = $lines->reject(fn (OrderLine $line) => in_array($line->status, ['cancelled', 'returned'], true));
        $subtotal = (float) $payableLines->sum('line_subtotal');
        $taxAmount = (float) $payableLines->sum('tax_amount');
        $discountAmount = min($subtotal, (float) $request->query('discount_amount', 0));
        $grandTotal = max(0, $subtotal - $discountAmount) + $taxAmount;

        return view('prints.pos-document', [
            'document' => [
                'type' => 'invoice',
                'title' => 'Invoice',
                'subtitle' => 'CUSTOMER COPY',
                'status' => 'preview',
                'no' => $diningSession->session_no,
                'billName' => $billGroup !== '' ? $billGroup : null,
                'jobNo' => null,
                'printerName' => 'Browser Preview',
                'branch' => $this->documentBranch($diningSession->branch),
                'seat' => $diningSession->diningResource?->name,
                'date' => now()->format('Y-m-d H:i'),
                'staff' => auth()->user()?->name,
                'isReprint' => false,
                'isCancel' => false,
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'tax' => $taxAmount,
                'grandTotal' => $grandTotal,
                'paidAmount' => 0,
                'balanceAmount' => $grandTotal,
                'lines' => $lines
                    ->map(function (OrderLine $line): array {
                        $isNoCharge = in_array($line->status, ['cancelled', 'returned'], true);

                        return [
                            'name' => $line->menu_name_snapshot,
                            'quantity' => (float) $line->quantity,
                            'unitPrice' => $isNoCharge ? 0 : (float) $line->unit_price,
                            'total' => $isNoCharge ? 0 : (float) $line->line_total,
                            'note' => $line->note,
                            'status' => $line->status,
                        ];
                    })
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function previewInvoiceDocument(DiningSession $diningSession, Invoice $invoice, string $documentType)
    {
        abort_if((int) $invoice->dining_session_id !== (int) $diningSession->id, 403);
        abort_if($documentType === 'receipt' && $invoice->status !== 'paid', 422, 'Receipt is available after payment is done.');

        $invoice->load(['branch', 'lines', 'payments.paymentMethod', 'diningSession.diningResource']);
        $latestPayment = $invoice->payments->sortByDesc('id')->first();

        return view('prints.pos-document', [
            'document' => [
                'type' => $documentType,
                'title' => $documentType === 'receipt' ? 'Receipt' : 'Invoice',
                'subtitle' => $documentType === 'receipt' ? 'PAID CUSTOMER RECEIPT' : 'CUSTOMER INVOICE',
                'status' => $invoice->status,
                'no' => $invoice->invoice_no,
                'jobNo' => null,
                'printerName' => 'Browser Preview',
                'branch' => $this->documentBranch($invoice->branch),
                'seat' => $invoice->diningSession?->diningResource?->name,
                'date' => ($invoice->issued_at ?? $invoice->created_at)?->format('Y-m-d H:i'),
                'staff' => auth()->user()?->name,
                'isReprint' => false,
                'isCancel' => false,
                'subtotal' => (float) $invoice->subtotal,
                'discount' => (float) $invoice->discount_amount,
                'tax' => (float) $invoice->tax_amount,
                'grandTotal' => (float) $invoice->grand_total,
                'paidAmount' => (float) $invoice->paid_amount,
                'balanceAmount' => (float) $invoice->balance_amount,
                'paymentMethod' => $this->paymentLabel($latestPayment),
                'paymentCurrency' => $latestPayment?->currency,
                'receivedAmount' => $latestPayment ? (float) $latestPayment->received_amount : null,
                'changeUsdAmount' => $latestPayment ? (float) $latestPayment->change_usd_amount : null,
                'changeKhrAmount' => $latestPayment ? (float) $latestPayment->change_khr_amount : null,
                'paidAt' => $latestPayment?->paid_at?->format('Y-m-d H:i') ?? $invoice->paid_at?->format('Y-m-d H:i'),
                'lines' => $invoice->lines
                    ->map(function ($line): array {
                        $isNoCharge = in_array($line->status, ['cancelled', 'returned'], true);

                        return [
                            'name' => $line->menu_name_snapshot,
                            'quantity' => (float) $line->quantity,
                            'unitPrice' => $isNoCharge ? 0 : (float) $line->unit_price,
                            'total' => $isNoCharge ? 0 : (float) $line->line_total,
                            'note' => $line->note,
                            'status' => $line->status,
                        ];
                    })
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function cancelPrintedLine(Request $request, DiningSession $diningSession, OrderLine $orderLine)
    {
        $this->ensureLineBelongsToSession($diningSession, $orderLine);

        $orderLine->loadMissing(['order.diningSession.diningResource', 'menu.printer', 'invoiceLines']);

        abort_if(
            $orderLine->order?->status === 'draft',
            422,
            'Only printed order lines can be cancelled here.'
        );

        abort_if(
            $orderLine->status === 'cancelled',
            422,
            'This menu line has already been cancelled.'
        );

        abort_if(
            $orderLine->invoiceLines->isNotEmpty(),
            422,
            'This menu line cannot be cancelled after check bill.'
        );

        abort_if(
            $diningSession->invoices()->where('status', '!=', 'cancelled')->exists(),
            422,
            'Printed menu lines cannot be cancelled after check bill.'
        );

        DB::transaction(function () use ($request, $orderLine) {
            $orderLine->update(['status' => 'cancelled']);

            if (! $orderLine->order->orderLines()->where('status', '!=', 'cancelled')->exists()) {
                $orderLine->order->update([
                    'status' => 'cancelled',
                    'cancelled_by' => $request->user()?->id,
                    'cancel_reason' => 'All printed lines cancelled before check bill.',
                ]);
            }

            $this->createCancelSlipPrintJob($orderLine, $request);
        });

        return back()->with('success', 'Cancel slip has been queued.');
    }

    public function returnPrintedLine(Request $request, DiningSession $diningSession, OrderLine $orderLine)
    {
        $this->ensureLineBelongsToSession($diningSession, $orderLine);

        $data = $request->validate([
            'quantity' => ['nullable', 'numeric', 'min:0.0001'],
        ]);

        $orderLine->loadMissing(['order.diningSession.diningResource', 'menu.printer', 'invoiceLines']);

        abort_if(
            $orderLine->order?->status === 'draft',
            422,
            'Only printed order lines can be returned here.'
        );

        abort_if(
            in_array($orderLine->status, ['cancelled', 'returned'], true),
            422,
            'This menu line has already been cancelled or returned.'
        );

        abort_if(
            $orderLine->invoiceLines->isNotEmpty(),
            422,
            'This menu line cannot be returned after check bill.'
        );

        abort_if(
            $diningSession->invoices()->where('status', '!=', 'cancelled')->exists(),
            422,
            'Printed menu lines cannot be returned after check bill.'
        );

        $availableQty = (float) $orderLine->quantity;
        $returnQty = min($availableQty, (float) ($data['quantity'] ?? $availableQty));

        abort_if($returnQty <= 0 || $returnQty > $availableQty, 422, 'Return quantity is not valid.');

        DB::transaction(function () use ($request, $orderLine, $returnQty, $availableQty) {
            $returnedLine = $orderLine;

            if ($returnQty < $availableQty) {
                $orderLine->quantity = $availableQty - $returnQty;
                $this->calculateLine($orderLine);
                $orderLine->save();

                $returnedLine = $orderLine->replicate();
                $returnedLine->quantity = $returnQty;
                $returnedLine->discount_amount = 0;
                $returnedLine->tax_amount = 0;
                $returnedLine->line_subtotal = 0;
                $returnedLine->line_total = 0;
                $returnedLine->status = 'returned';
                $returnedLine->created_at = now();
                $returnedLine->updated_at = now();
                $returnedLine->save();
                $returnedLine->setRelation('order', $orderLine->order);
                $returnedLine->setRelation('menu', $orderLine->menu);
            } else {
                $orderLine->update([
                    'status' => 'returned',
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'line_subtotal' => 0,
                    'line_total' => 0,
                ]);
            }

            $this->createReturnSlipPrintJob($returnedLine, $request);
        });

        return back()->with('success', 'Return slip has been queued.');
    }

    public function settle(Request $request, DiningSession $diningSession)
    {
        $data = $request->validate([
            'method' => ['required', 'string', 'max:50'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'membership_card_id' => ['nullable', 'exists:membership_cards,id'],
            'currency' => ['required', 'in:USD,KHR'],
            'received_amount' => ['nullable', 'numeric', 'min:0'],
            'operation_status' => ['required', 'in:invoice,invoice_receipt_done'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'change_usd_amount' => ['nullable', 'integer', 'min:0'],
            'change_khr_amount' => ['nullable', 'integer', 'min:0'],
            'bill_group' => ['nullable', 'string', 'max:50'],
        ]);

        $activePosSession = $this->activePosSession($request);

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first.');
        }

        DB::transaction(function () use ($diningSession, $activePosSession, $data, $request) {
            $billGroup = trim((string) ($data['bill_group'] ?? ''));
            $orders = $diningSession->orders()
                ->with(['orderLines' => fn ($query) => $query
                    ->whereDoesntHave('invoiceLines')
                    ->when($billGroup !== '', fn ($lineQuery) => $this->applyBillGroupFilter($lineQuery, $billGroup))])
                ->where('status', '!=', 'draft')
                ->whereHas('orderLines', fn ($query) => $query
                    ->whereDoesntHave('invoiceLines')
                    ->when($billGroup !== '', fn ($lineQuery) => $this->applyBillGroupFilter($lineQuery, $billGroup)))
                ->get();

            $orderLines = $orders->flatMap->orderLines;

            if ($orderLines->isEmpty()) {
                abort(422, 'Please confirm at least one order before checking bill.');
            }

            $payableLines = $orderLines->reject(fn (OrderLine $line) => in_array($line->status, ['cancelled', 'returned'], true));
            $subtotal = (float) $payableLines->sum('line_subtotal');
            $taxAmount = (float) $payableLines->sum('tax_amount');
            $discountAmount = min($subtotal, (float) ($data['discount_amount'] ?? 0));
            $grandTotal = max(0, $subtotal - $discountAmount) + $taxAmount;
            $isPayLater = $data['operation_status'] === 'invoice';
            $exchangeRate = $this->latestExchangeRate($activePosSession);
            $paidAmount = 0;
            $invoiceStatus = 'pay_later';
            $paymentAmount = 0;
            $receivedAmount = 0;
            $changeUsdAmount = 0;
            $changeKhrAmount = 0;

            $invoice = new Invoice([
                'company_id' => $diningSession->company_id,
                'branch_id' => $diningSession->branch_id,
                'pos_terminal_id' => $diningSession->pos_terminal_id,
                'pos_open_date' => $diningSession->pos_open_date
                    ?? $activePosSession->opened_at?->toDateString()
                    ?? now()->toDateString(),
                'dining_session_id' => $diningSession->id,
                'customer_id' => $diningSession->customer_id,
                'invoice_no' => DocumentNumber::make(Invoice::class, 'invoice_no', 'IN'),
                'currency' => 'USD',
                'exchange_rate_snapshot' => $exchangeRate,
                'issued_at' => now(),
                'issued_by' => $request->user()->id,
            ]);

            if (! $isPayLater) {
                $method = $this->paymentMethodFor(
                    $data['method'],
                    $data['currency'],
                    $activePosSession,
                    $data['payment_method_id'] ?? null,
                );

                abort_if(! $method, 422, 'Please select a valid payment method.');

                $isMembershipCard = $method->method_type === 'card' || str_starts_with($data['method'], 'member-card-');
                $isCash = str_starts_with($data['method'], 'cash-');
                $targetAmount = $data['currency'] === 'KHR' ? $grandTotal * $exchangeRate : $grandTotal;
                $receivedAmount = (float) ($data['received_amount'] ?? $targetAmount);
                $changeUsdAmount = $isCash ? (float) floor((float) ($data['change_usd_amount'] ?? 0)) : 0;
                $changeKhrAmount = $isCash ? $this->roundDownKhrChange((float) ($data['change_khr_amount'] ?? 0)) : 0;

                abort_if($receivedAmount <= 0, 422, 'Payment amount must be greater than zero.');

                $cardBalance = null;
                if ($isMembershipCard) {
                    abort_if(! $diningSession->customer_id, 422, 'Please assign a customer before using membership card payment.');
                    $cardBalance = $this->membershipCardBalanceForPayment(
                        (int) ($data['membership_card_id'] ?? 0),
                        (int) $diningSession->customer_id,
                        $diningSession->company_id,
                        $data['currency'],
                    );
                    abort_if(! $cardBalance, 422, 'Please select a valid active membership card.');
                    abort_if($receivedAmount > $targetAmount + 0.01, 422, 'Membership card payment cannot be greater than the invoice balance.');
                    abort_if((float) $cardBalance->balance + 0.0001 < $receivedAmount, 422, 'Membership card balance is not enough for this payment.');
                }

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
                $paidAmount = round(min($grandTotal, max(0, $netReceivedUsd)), 2);

                abort_if($paidAmount <= 0, 422, 'Payment amount must be greater than zero.');

                $paymentAmount = $data['currency'] === 'KHR' ? $paidAmount * $exchangeRate : $paidAmount;
                $invoiceStatus = $paidAmount >= $grandTotal ? 'paid' : 'partially_paid';
            }

            $invoice->fill([
                'status' => $invoiceStatus,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'balance_amount' => max(0, $grandTotal - $paidAmount),
                'paid_at' => $invoiceStatus === 'paid' ? now() : null,
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
                        'status' => $line->status,
                        'note' => $line->note,
                    ]);
                }
            }

            if (! $isPayLater) {
                $payment = Payment::create([
                    'company_id' => $diningSession->company_id,
                    'branch_id' => $diningSession->branch_id,
                    'invoice_id' => $invoice->id,
                    'payment_method_id' => $method?->id,
                    'payment_no' => DocumentNumber::make(Payment::class, 'payment_no', 'PY'),
                    'status' => $invoiceStatus === 'paid' ? 'paid' : 'partial',
                    'currency' => $data['currency'],
                    'amount_paid' => $paymentAmount,
                    'received_amount' => $receivedAmount,
                    'change_usd_amount' => floor($changeUsdAmount),
                    'change_khr_amount' => $changeKhrAmount,
                    'exchange_rate_snapshot' => $exchangeRate,
                    'amount_usd_equivalent' => $paidAmount,
                    'amount_khr_equivalent' => $paidAmount * $exchangeRate,
                    'paid_at' => now(),
                    'received_by' => $request->user()->id,
                ]);

                if ($isMembershipCard && $cardBalance) {
                    $this->recordMembershipCardPayment(
                        $cardBalance,
                        $payment,
                        $invoice,
                        $data['currency'],
                        $paymentAmount,
                        $paidAmount,
                        $exchangeRate,
                        $request->user()->id,
                    );
                }

                $this->recordPosSessionPaymentTotals(
                    $activePosSession,
                    $data['method'],
                    $data['currency'],
                    $grandTotal,
                    $paymentAmount,
                    $receivedAmount,
                    $changeUsdAmount,
                    $changeKhrAmount,
                    $exchangeRate,
                );
            } else {
                $activePosSession->increment('total_pay_later_usd', $grandTotal);
                $activePosSession->increment('total_pay_later_khr', $grandTotal * $exchangeRate);
            }

            $hasRemainingBillableLines = $diningSession->orders()
                ->whereNotIn('status', ['draft', 'cancelled'])
                ->whereHas('orderLines', fn ($query) => $query
                    ->where('status', '!=', 'cancelled')
                    ->whereDoesntHave('invoiceLines'))
                ->exists();

            $diningSession->update([
                'status' => $hasRemainingBillableLines ? 'open' : ($invoiceStatus === 'paid' ? 'paid' : 'invoiced'),
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
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_name' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($diningSession, $activePosSession, $data) {
            $phoneNumber = trim((string) ($data['customer_phone'] ?? ''));
            $customerName = trim((string) ($data['customer_name'] ?? ''));
            $customerId = null;

            if (! empty($data['customer_id'])) {
                $customer = Customer::query()
                    ->where('id', $data['customer_id'])
                    ->where('company_id', $activePosSession->company_id)
                    ->where('is_active', true)
                    ->first();

                abort_if(! $customer, 422, 'Please select a valid customer.');

                $customerId = $customer->id;
            } elseif ($phoneNumber !== '') {
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
        $hasSelectedItems = $diningSession->orders()
            ->whereHas('orderLines')
            ->exists();

        abort_if(
            $hasSelectedItems && ! in_array($diningSession->status, ['invoiced', 'paid', 'pay_later'], true),
            422,
            'Please create an invoice or receive payment before closing this order.'
        );

        abort_if(
            $hasSelectedItems && $this->hasUnbilledConfirmedOrders($diningSession),
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
                'pos_open_date' => $diningSession->pos_open_date
                    ?? $diningSession->opened_at?->toDateString()
                    ?? now()->toDateString(),
                'menu_price_list_id' => $diningSession->menu_price_list_id,
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

    private function openSessionForResource(
        DiningResource $resource,
        DiningSession $sourceSession,
        PosSession $activePosSession,
        Request $request
    ): DiningSession {
        $session = DiningSession::query()
            ->where('dining_resource_id', $resource->id)
            ->where('branch_id', $activePosSession->branch_id)
            ->where('status', 'open')
            ->latest()
            ->lockForUpdate()
            ->first();

        if ($session) {
            return $session;
        }

        $hasLockedSession = DiningSession::query()
            ->where('dining_resource_id', $resource->id)
            ->where('branch_id', $activePosSession->branch_id)
            ->whereIn('status', ['invoiced', 'pay_later', 'paid'])
            ->exists();

        abort_if($hasLockedSession, 422, 'Selected table already has a checked bill.');

        $session = DiningSession::create([
            'company_id' => $activePosSession->company_id,
            'branch_id' => $activePosSession->branch_id,
            'pos_terminal_id' => $activePosSession->pos_terminal_id,
            'pos_open_date' => $activePosSession->opened_at?->toDateString() ?? now()->toDateString(),
            'customer_id' => $sourceSession->customer_id,
            'dining_resource_id' => $resource->id,
            'menu_price_list_id' => $sourceSession->menu_price_list_id,
            'session_no' => DocumentNumber::make(DiningSession::class, 'session_no', 'DS'),
            'guest_count' => null,
            'status' => 'open',
            'opened_at' => now(),
            'opened_by' => $request->user()?->id,
            'note' => 'Created from manage orders split of '.$sourceSession->session_no,
        ]);

        $resource->update(['status' => 'occupied']);

        return $session;
    }

    private function openManagedOrderForSession(DiningSession $targetSession, Order $sourceOrder): Order
    {
        if ((int) $sourceOrder->dining_session_id === (int) $targetSession->id) {
            return $sourceOrder;
        }

        return Order::query()
            ->where('dining_session_id', $targetSession->id)
            ->where('status', $sourceOrder->status)
            ->where('note', 'Managed order split')
            ->latest()
            ->first()
            ?? Order::create([
                'company_id' => $targetSession->company_id,
                'branch_id' => $targetSession->branch_id,
                'dining_session_id' => $targetSession->id,
                'pos_open_date' => $targetSession->pos_open_date
                    ?? $targetSession->opened_at?->toDateString()
                    ?? now()->toDateString(),
                'menu_price_list_id' => $targetSession->menu_price_list_id,
                'order_no' => $this->makeDiningSessionOrderNumber($targetSession),
                'status' => $sourceOrder->status,
                'sent_to_kitchen_at' => $sourceOrder->sent_to_kitchen_at,
                'created_by' => auth()->id(),
                'note' => 'Managed order split',
            ]);
    }

    private function syncResourceStatusesAfterManage(DiningSession $sourceSession): bool
    {
        $hasRemainingOrders = $sourceSession->orders()
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereHas('orderLines')
            ->exists();

        if (! $hasRemainingOrders) {
            $sourceSession->update(['status' => 'cancelled']);
            $sourceSession->diningResource()->update(['status' => 'available']);

            return true;
        }

        return false;
    }

    private function calculateLine(OrderLine $line): void
    {
        $subtotal = ((float) $line->quantity * (float) $line->unit_price) - (float) $line->discount_amount;
        $taxAmount = $subtotal * ((float) $line->tax_rate_snapshot / 100);

        $line->line_subtotal = $subtotal;
        $line->tax_amount = $taxAmount;
        $line->line_total = $subtotal + $taxAmount;
    }

    private function applyBillGroupFilter($query, string $billGroup)
    {
        if (strcasecmp($billGroup, 'Bill A') === 0) {
            return $query->where(function ($groupQuery) use ($billGroup) {
                $groupQuery
                    ->where('bill_group', $billGroup)
                    ->orWhereNull('bill_group')
                    ->orWhere('bill_group', '');
            });
        }

        return $query->where('bill_group', $billGroup);
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
            ->with('prices')
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
                $unitPrice = $this->menuPriceFor($menu, $order->menu_price_list_id, $order->branch_id);

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
                    'bill_group' => 'Bill A',
                    'note' => $note ?: null,
                    'status' => 'ordered',
                ]);

                $this->calculateLine($line);
                $line->save();
            });
    }

    private function createPrintJobsForOrder(Order $order, Request $request): void
    {
        $defaultPrinters = Printer::query()
            ->where('company_id', $order->company_id)
            ->where('branch_id', $order->branch_id)
            ->where('is_active', true)
            ->get()
            ->groupBy('printer_role')
            ->map(fn ($printers) => $printers->firstWhere('is_default', true) ?? $printers->first());

        $templates = PrintTemplate::query()
            ->where('company_id', $order->company_id)
            ->where(function ($query) use ($order) {
                $query->whereNull('branch_id')
                    ->orWhere('branch_id', $order->branch_id);
            })
            ->where('is_active', true)
            ->where('is_default', true)
            ->get()
            ->keyBy('template_type');

        $order->orderLines
            ->filter(fn (OrderLine $line) => $line->menu && ($line->menu->print_route ?? 'none') !== 'none')
            ->groupBy(function (OrderLine $line) use ($defaultPrinters) {
                $route = $line->menu->print_route ?: 'kitchen';
                $printer = $line->menu->printer ?: $defaultPrinters->get($route);

                return ($printer?->id ?: 'route-'.$route).'|'.$route;
            })
            ->each(function ($lines, string $key) use ($order, $request, $defaultPrinters, $templates) {
                [, $route] = explode('|', $key, 2);
                $firstLine = $lines->first();
                $printer = $firstLine->menu->printer ?: $defaultPrinters->get($route);
                $jobType = match ($route) {
                    'stock' => 'stock_ticket',
                    'bar' => 'bar_ticket',
                    'cashier' => 'receipt',
                    default => 'kitchen_ticket',
                };

                PrintJob::create([
                    'company_id' => $order->company_id,
                    'branch_id' => $order->branch_id,
                    'printer_id' => $printer?->id,
                    'print_template_id' => $templates->get($jobType)?->id ?? $templates->get('kitchen_ticket')?->id,
                    'job_no' => DocumentNumber::make(PrintJob::class, 'job_no', 'PJ'),
                    'job_type' => $jobType,
                    'status' => 'pending',
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'reference_no' => $order->order_no,
                    'payload' => [
                        'route' => $route,
                        'printer' => $printer ? [
                            'name' => $printer->name,
                            'code' => $printer->code,
                            'connection_type' => $printer->connection_type,
                            'network_protocol' => $printer->network_protocol,
                            'ip_address' => $printer->ip_address,
                            'port' => $printer->port,
                            'paper_size' => $printer->paper_size,
                        ] : null,
                        'order' => [
                            'id' => $order->id,
                            'order_no' => $order->order_no,
                            'seat' => $order->diningSession?->diningResource?->name,
                            'sent_at' => $order->sent_to_kitchen_at?->toDateTimeString() ?? now()->toDateTimeString(),
                            'created_by' => $request->user()?->name,
                        ],
                        'lines' => $lines->map(fn (OrderLine $line): array => [
                            'order_line_id' => $line->id,
                            'menu_id' => $line->menu_id,
                            'name' => $line->menu_name_snapshot,
                            'quantity' => (float) $line->quantity,
                            'note' => $line->note,
                        ])->values()->all(),
                    ],
                ]);
            });
    }

    private function createCancelSlipPrintJob(OrderLine $orderLine, Request $request): void
    {
        $order = $orderLine->order;
        $menu = $orderLine->menu;
        $route = $menu?->print_route ?: 'kitchen';

        if ($route === 'none') {
            return;
        }

        $defaultPrinter = Printer::query()
            ->where('company_id', $order->company_id)
            ->where('branch_id', $order->branch_id)
            ->where('printer_role', $route)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->first();
        $printer = $menu?->printer ?: $defaultPrinter;

        $template = PrintTemplate::query()
            ->where('company_id', $order->company_id)
            ->where(function ($query) use ($order) {
                $query->whereNull('branch_id')
                    ->orWhere('branch_id', $order->branch_id);
            })
            ->where('is_active', true)
            ->where('is_default', true)
            ->where('template_type', 'cancel_slip')
            ->first()
            ?? PrintTemplate::query()
                ->where('company_id', $order->company_id)
                ->where(function ($query) use ($order) {
                    $query->whereNull('branch_id')
                        ->orWhere('branch_id', $order->branch_id);
                })
                ->where('is_active', true)
                ->where('is_default', true)
                ->whereIn('template_type', ['kitchen_ticket', 'stock_ticket', 'bar_ticket'])
                ->first();

        PrintJob::create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'printer_id' => $printer?->id,
            'print_template_id' => $template?->id,
            'job_no' => DocumentNumber::make(PrintJob::class, 'job_no', 'PJ'),
            'job_type' => 'cancel_slip',
            'status' => 'pending',
            'reference_type' => 'order',
            'reference_id' => $order->id,
            'reference_no' => $order->order_no,
            'payload' => [
                'route' => $route,
                'is_cancel_slip' => true,
                'cancelled_at' => now()->toDateTimeString(),
                'cancelled_by' => $request->user()?->name,
                'printer' => $printer ? [
                    'name' => $printer->name,
                    'code' => $printer->code,
                    'connection_type' => $printer->connection_type,
                    'network_protocol' => $printer->network_protocol,
                    'ip_address' => $printer->ip_address,
                    'port' => $printer->port,
                    'paper_size' => $printer->paper_size,
                ] : null,
                'order' => [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'seat' => $order->diningSession?->diningResource?->name,
                    'sent_at' => now()->toDateTimeString(),
                    'created_by' => $request->user()?->name,
                ],
                'lines' => [[
                    'order_line_id' => $orderLine->id,
                    'menu_id' => $orderLine->menu_id,
                    'name' => $orderLine->menu_name_snapshot,
                    'quantity' => (float) $orderLine->quantity,
                    'note' => $orderLine->note,
                    'status' => 'cancelled',
                ]],
            ],
        ]);
    }

    private function createReturnSlipPrintJob(OrderLine $orderLine, Request $request): void
    {
        $order = $orderLine->order;
        $menu = $orderLine->menu;
        $route = $menu?->print_route ?: 'kitchen';

        if ($route === 'none') {
            return;
        }

        $defaultPrinter = Printer::query()
            ->where('company_id', $order->company_id)
            ->where('branch_id', $order->branch_id)
            ->where('printer_role', $route)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->first();
        $printer = $menu?->printer ?: $defaultPrinter;

        $template = PrintTemplate::query()
            ->where('company_id', $order->company_id)
            ->where(function ($query) use ($order) {
                $query->whereNull('branch_id')
                    ->orWhere('branch_id', $order->branch_id);
            })
            ->where('is_active', true)
            ->where('is_default', true)
            ->where('template_type', 'cancel_slip')
            ->first()
            ?? PrintTemplate::query()
                ->where('company_id', $order->company_id)
                ->where(function ($query) use ($order) {
                    $query->whereNull('branch_id')
                        ->orWhere('branch_id', $order->branch_id);
                })
                ->where('is_active', true)
                ->where('is_default', true)
                ->whereIn('template_type', ['kitchen_ticket', 'stock_ticket', 'bar_ticket'])
                ->first();

        PrintJob::create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'printer_id' => $printer?->id,
            'print_template_id' => $template?->id,
            'job_no' => DocumentNumber::make(PrintJob::class, 'job_no', 'PJ'),
            'job_type' => 'cancel_slip',
            'status' => 'pending',
            'reference_type' => 'order',
            'reference_id' => $order->id,
            'reference_no' => $order->order_no,
            'payload' => [
                'route' => $route,
                'is_return_slip' => true,
                'returned_at' => now()->toDateTimeString(),
                'returned_by' => $request->user()?->name,
                'printer' => $printer ? [
                    'name' => $printer->name,
                    'code' => $printer->code,
                    'connection_type' => $printer->connection_type,
                    'network_protocol' => $printer->network_protocol,
                    'ip_address' => $printer->ip_address,
                    'port' => $printer->port,
                    'paper_size' => $printer->paper_size,
                ] : null,
                'order' => [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'seat' => $order->diningSession?->diningResource?->name,
                    'sent_at' => now()->toDateTimeString(),
                    'created_by' => $request->user()?->name,
                ],
                'lines' => [[
                    'order_line_id' => $orderLine->id,
                    'menu_id' => $orderLine->menu_id,
                    'name' => $orderLine->menu_name_snapshot,
                    'quantity' => (float) $orderLine->quantity,
                    'note' => $orderLine->note,
                    'status' => 'returned',
                ]],
            ],
        ]);
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

        $code = $method === 'membership-card'
            ? ($currency === 'KHR' ? 'MEMBER_CARD_KHR' : 'MEMBER_CARD_USD')
            : match ($method) {
                'cash-usd' => 'CASH_USD',
                'cash-khr' => 'CASH_KHR',
                'ebanking-usd' => 'EBANK_USD',
                'ebanking-khr' => 'EBANK_KHR',
                'member-card-usd' => 'MEMBER_CARD_USD',
                'member-card-khr' => 'MEMBER_CARD_KHR',
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

    private function roundDownKhrChange(float $amount): float
    {
        if ($amount <= 0) {
            return 0;
        }

        return floor($amount / 100) * 100;
    }

    private function documentBranch(?Branch $branch): array
    {
        return [
            'name' => $branch?->name,
            'phone' => $branch?->phone,
            'vatNumber' => $branch?->vat_number,
            'address' => $branch?->address,
            'logoUrl' => $branch?->logo ? Storage::url($branch->logo) : null,
            'paymentQrcodeUrl' => $branch?->payment_qrcode ? Storage::url($branch->payment_qrcode) : null,
        ];
    }

    private function paymentLabel(?Payment $payment): ?string
    {
        if (! $payment) {
            return null;
        }

        if ($payment->paymentMethod?->name) {
            return $payment->paymentMethod->name;
        }

        return trim(($payment->currency ?? '').' Payment') ?: null;
    }

    private function membershipCardsForCustomer(?int $customerId, PosSession $posSession): array
    {
        if (! $customerId) {
            return [];
        }

        return MembershipCard::query()
            ->with('balances')
            ->where('customer_id', $customerId)
            ->where('company_id', $posSession->company_id)
            ->where('status', 'active')
            ->get()
            ->map(fn (MembershipCard $card) => [
                'id' => $card->id,
                'customerId' => $card->customer_id,
                'cardNo' => $card->card_no,
                'cardName' => $card->card_name,
                'balances' => $card->balances
                    ->map(fn (MembershipCardBalance $balance) => [
                        'currency' => $balance->currency,
                        'balance' => (float) $balance->balance,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    private function customerOptions(PosSession $posSession): array
    {
        return Customer::query()
            ->withCount(['membershipCards' => fn ($query) => $query
                ->where('status', 'active')])
            ->where('company_id', $posSession->company_id)
            ->where('is_active', true)
            ->orderByDesc('membership_cards_count')
            ->orderBy('name')
            ->limit(300)
            ->get()
            ->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->name ?: 'Unnamed Customer',
                'phone' => $customer->phone_number,
                'cardCount' => (int) $customer->membership_cards_count,
            ])
            ->values()
            ->all();
    }

    private function membershipCardBalanceForPayment(
        int $membershipCardId,
        int $customerId,
        int $companyId,
        string $currency,
    ): ?MembershipCardBalance {
        if ($membershipCardId <= 0) {
            return null;
        }

        $balance = MembershipCardBalance::query()
            ->with('membershipCard')
            ->where('currency', $currency)
            ->whereHas('membershipCard', function ($query) use ($membershipCardId, $customerId, $companyId) {
                $query->where('id', $membershipCardId)
                    ->where('customer_id', $customerId)
                    ->where('company_id', $companyId)
                    ->where('status', 'active');
            })
            ->lockForUpdate()
            ->first();

        if ($balance) {
            try {
                app(MembershipCardLedger::class)->assertBalanceIsVerified($balance);
            } catch (\RuntimeException $exception) {
                abort(422, $exception->getMessage());
            }
        }

        return $balance;
    }

    private function recordMembershipCardPayment(
        MembershipCardBalance $cardBalance,
        Payment $payment,
        Invoice $invoice,
        string $currency,
        float $amount,
        float $amountUsd,
        float $exchangeRate,
        ?int $performedBy,
    ): void {
        try {
            app(MembershipCardLedger::class)->assertBalanceIsVerified($cardBalance);

            app(MembershipCardLedger::class)->debit($cardBalance->membershipCard, [
                'branch_id' => $invoice->branch_id,
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'transaction_type' => 'payment',
                'currency' => $currency,
                'amount' => $amount,
                'exchange_rate_snapshot' => $exchangeRate,
                'amount_usd_equivalent' => $amountUsd,
                'amount_khr_equivalent' => $amountUsd * $exchangeRate,
                'transacted_at' => now(),
                'performed_by' => $performedBy,
                'payload' => [
                    'invoice_no' => $invoice->invoice_no,
                    'payment_no' => $payment->payment_no,
                ],
            ]);
        } catch (\RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }
    }

    private function paymentMethodsFor(PosSession $posSession): array
    {
        $query = PaymentMethod::query()
            ->where('company_id', $posSession->company_id)
            ->where('is_active', true)
            ->whereIn('currency', ['USD', 'KHR'])
            ->whereIn('method_type', ['cash', 'bank', 'card'])
            ->where(function ($query) use ($posSession) {
                $query->whereNull('branch_id')
                    ->orWhere('branch_id', $posSession->branch_id);
            });

        $paymentMethods = (clone $query)
            ->orderByRaw("case method_type when 'cash' then 0 when 'bank' then 1 when 'card' then 2 else 3 end")
            ->orderBy('currency')
            ->get();

        if ($paymentMethods->isEmpty()) {
            $paymentMethods = PaymentMethod::query()
                ->where('company_id', $posSession->company_id)
                ->where('is_active', true)
                ->whereIn('currency', ['USD', 'KHR'])
                ->whereIn('method_type', ['cash', 'bank', 'card'])
                ->orderByRaw("case method_type when 'cash' then 0 when 'bank' then 1 when 'card' then 2 else 3 end")
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

    private function printOrdersFor(DiningSession $diningSession): array
    {
        $orderIds = $diningSession->orders()
            ->where('status', '!=', 'draft')
            ->pluck('id');

        if ($orderIds->isEmpty()) {
            return [];
        }

        $hasInvoice = $diningSession->invoices()
            ->where('status', '!=', 'cancelled')
            ->exists();
        $orderLines = OrderLine::query()
            ->with('invoiceLines:id,order_line_id')
            ->whereIn('order_id', $orderIds)
            ->get(['id', 'status'])
            ->keyBy('id');

        return PrintJob::query()
            ->with('printer:id,name,code,printer_role')
            ->where('reference_type', 'order')
            ->whereIn('reference_id', $orderIds)
            ->latest('id')
            ->get()
            ->groupBy('reference_id')
            ->map(function ($jobs, $orderId) use ($orderLines, $hasInvoice): array {
                $firstJob = $jobs->first();

                return [
                    'orderId' => (int) $orderId,
                    'orderNo' => $firstJob->reference_no ?? 'Order',
                    'printedAt' => $firstJob->created_at?->format('Y-m-d H:i'),
                    'printers' => $jobs
                        ->groupBy(fn (PrintJob $job): string => (string) ($job->printer_id ?? 'default-'.$job->job_type))
                        ->map(function ($printerJobs) use ($orderLines, $hasInvoice): array {
                            $latestJob = $printerJobs->first();
                            $payload = $latestJob->payload ?? [];
                            $printer = $latestJob->printer;
                            $lines = $printerJobs
                                ->sortBy('id')
                                ->flatMap(fn (PrintJob $job) => collect(data_get($job->payload ?? [], 'lines', [])))
                                ->unique(fn (array $line): int => (int) ($line['order_line_id'] ?? 0));

                            return [
                                'jobId' => $latestJob->id,
                                'jobNo' => $latestJob->job_no,
                                'printerName' => $printer?->name ?? data_get($payload, 'printer.name', 'Default Printer'),
                                'printerRole' => $printer?->printer_role ?? data_get($payload, 'route', 'general'),
                                'status' => $latestJob->status,
                                'isReprint' => (bool) data_get($payload, 'is_reprint', false),
                                'reprintOf' => data_get($payload, 'reprint_of'),
                                'printedAt' => $latestJob->created_at?->format('Y-m-d H:i'),
                                'lines' => $lines
                                    ->map(function (array $line) use ($orderLines, $hasInvoice): array {
                                        $lineId = (int) ($line['order_line_id'] ?? 0);
                                        $orderLine = $orderLines->get($lineId);
                                        $status = (string) ($orderLine?->status ?? $line['status'] ?? 'ordered');

                                        return [
                                            'id' => $lineId,
                                            'name' => (string) ($line['name'] ?? 'Menu item'),
                                            'quantity' => (float) ($orderLine?->quantity ?? $line['quantity'] ?? 0),
                                            'note' => $line['note'] ?? null,
                                            'status' => $status,
                                            'canCancel' => $lineId > 0
                                                && ! in_array($status, ['cancelled', 'returned'], true)
                                                && ! $hasInvoice
                                                && $orderLine
                                                && $orderLine->invoiceLines->isEmpty(),
                                            'canReturn' => $lineId > 0
                                                && ! in_array($status, ['cancelled', 'returned'], true)
                                                && ! $hasInvoice
                                                && $orderLine
                                                && $orderLine->invoiceLines->isEmpty(),
                                        ];
                                    })
                                    ->values()
                                    ->all(),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function formatOrder(Order $order): array
    {
        $lines = $order->orderLines;
        $uninvoicedLines = $lines->filter(fn (OrderLine $line) => $line->invoiceLines->isEmpty());
        $billableLines = $uninvoicedLines->reject(fn (OrderLine $line) => in_array($line->status, ['cancelled', 'returned'], true));
        $hasInvoicedLines = $lines->contains(fn (OrderLine $line) => $line->invoiceLines->isNotEmpty());
        $hasBillableLines = $uninvoicedLines->isNotEmpty();
        $invoice = ! $hasBillableLines && $hasInvoicedLines
            ? Invoice::query()
                ->whereHas('lines', fn ($query) => $query->where('order_id', $order->id))
                ->where('status', '!=', 'cancelled')
                ->latest()
                ->first(['id', 'invoice_no', 'status'])
            : null;

        return [
            'id' => $order->id,
            'order_no' => $order->order_no,
            'status' => $order->status,
            'invoice_no' => $invoice?->invoice_no,
            'invoice_status' => $invoice?->status,
            'subtotal' => (float) $billableLines->sum('line_subtotal'),
            'tax_amount' => (float) $billableLines->sum('tax_amount'),
            'total_amount' => (float) $billableLines->sum('line_total'),
            'created_at' => $order->created_at?->format('Y-m-d H:i'),
            'lines' => $lines->map(function ($line): array {
                $isNoCharge = in_array($line->status, ['cancelled', 'returned'], true);

                return [
                    'id' => $line->id,
                    'menu_id' => $line->menu_id,
                    'menu_name' => $line->menu_name_snapshot,
                    'qty' => (float) $line->quantity,
                    'unit_price' => $isNoCharge ? 0 : (float) $line->unit_price,
                    'subtotal' => $isNoCharge ? 0 : (float) $line->line_subtotal,
                    'tax_amount' => $isNoCharge ? 0 : (float) $line->tax_amount,
                    'total_amount' => $isNoCharge ? 0 : (float) $line->line_total,
                    'note' => $line->note,
                    'status' => $line->status,
                    'bill_group' => $line->bill_group ?: 'Bill A',
                    'has_invoice' => $line->invoiceLines->isNotEmpty(),
                ];
            }),
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
                'total_amount' => in_array($line->status, ['cancelled', 'returned'], true) ? 0 : (float) $line->line_total,
                'note' => $line->note,
                'status' => $line->status,
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

    private function menuPriceFor(Menu $menu, ?int $priceListId, int $branchId): float
    {
        $query = MenuPrice::query()
            ->where('menu_id', $menu->id)
            ->where('is_active', true)
            ->where(function ($query) use ($branchId): void {
                $query->where('branch_id', $branchId)
                    ->orWhereNull('branch_id');
            });

        if ($priceListId) {
            $price = (clone $query)
                ->where('menu_price_list_id', $priceListId)
                ->orderByRaw('case when branch_id = ? then 0 when branch_id is null then 1 else 2 end', [$branchId])
                ->value('price');

            if ($price !== null) {
                return (float) $price;
            }
        }

        $defaultListId = MenuPriceList::query()
            ->where('company_id', $menu->company_id)
            ->where('is_active', true)
            ->where('is_default', true)
            ->where(function ($query) use ($branchId): void {
                $query->where('branch_id', $branchId)
                    ->orWhereNull('branch_id');
            })
            ->orderByRaw('case when branch_id = ? then 0 when branch_id is null then 1 else 2 end', [$branchId])
            ->value('id');

        if ($defaultListId) {
            $price = (clone $query)
                ->where('menu_price_list_id', $defaultListId)
                ->orderByRaw('case when branch_id = ? then 0 when branch_id is null then 1 else 2 end', [$branchId])
                ->value('price');

            if ($price !== null) {
                return (float) $price;
            }
        }

        $price = (clone $query)
            ->where('is_default', true)
            ->orderByRaw('case when branch_id = ? then 0 when branch_id is null then 1 else 2 end', [$branchId])
            ->value('price');

        return (float) ($price ?? $menu->base_price ?? 0);
    }
}
