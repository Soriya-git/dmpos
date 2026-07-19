<?php

namespace App\Http\Controllers\StockCustomer;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Item;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockLog;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferLine;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StockCustomerController extends Controller
{
    public function index(Request $request): Response
    {
        [$companyId, $branchId] = $this->scope($request);

        $transfers = StockTransfer::query()
            ->with([
                'invoice.customer:id,name,phone_number',
                'creator:id,name',
                'approver:id,name',
                'canceller:id,name',
                'toLocation:id,name,code',
                'lines.item:id,name,code',
                'lines.unit:id,name,code',
                'lines.toLocation:id,name,code',
            ])
            ->withCount('lines')
            ->where('company_id', $companyId)
            ->where('from_branch_id', $branchId)
            ->where('transfer_type', 'customer_stock_keep')
            ->latest('transfer_date')
            ->latest()
            ->get()
            ->map(fn (StockTransfer $transfer): array => $this->formatTransfer($transfer));

        return Inertia::render('StockCustomer/Index', [
            'keeps' => $transfers,
            'stats' => [
                'pendingInvoices' => $this->formattedInvoices($companyId, $branchId)->count(),
                'activeKeeps' => $transfers->whereIn('status', ['draft', 'submitted'])->count(),
                'receivedKeeps' => $transfers->where('status', 'received')->count(),
                'totalQuantity' => $transfers->sum('total_quantity'),
            ],
        ]);
    }

    public function invoices(Request $request): Response
    {
        [$companyId, $branchId] = $this->scope($request);

        return Inertia::render('StockCustomer/CompleteStockCus', [
            'invoices' => $this->formattedInvoices($companyId, $branchId),
        ]);
    }

    public function create(Request $request): Response
    {
        [$companyId, $branchId] = $this->scope($request);

        $keep = null;
        $invoice = null;

        if ($request->integer('stock_transfer_id')) {
            $keep = StockTransfer::query()
                ->with(['invoice.customer', 'invoice.lines.menu.item.unit', 'lines.toLocation'])
                ->where('company_id', $companyId)
                ->where('from_branch_id', $branchId)
                ->where('transfer_type', 'customer_stock_keep')
                ->where('status', 'draft')
                ->findOrFail($request->integer('stock_transfer_id'));
            $invoice = $keep->invoice;
        }

        if ($request->integer('invoice_id')) {
            $invoice = $this->invoiceQuery($companyId, $branchId)
                ->whereKey($request->integer('invoice_id'))
                ->first();
        }

        return Inertia::render('StockCustomer/CreateStockCus', [
            'nextTransferNo' => $keep?->transfer_no ?? DocumentNumber::preview(StockTransfer::class, 'transfer_no', 'CS', $branchId),
            'keep' => $keep ? $this->formatEditableTransfer($keep) : null,
            'invoice' => $invoice ? $this->formatInvoice($invoice, $keep?->id) : null,
            'invoices' => $this->formattedInvoices($companyId, $branchId),
            'customerLocations' => $this->customerLocations($companyId, $branchId),
        ]);
    }

    public function store(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);
        $data = $this->validateKeep($request, $companyId);

        $transfer = DB::transaction(function () use ($request, $data, $companyId, $branchId): StockTransfer {
            $invoice = $this->invoiceQuery($companyId, $branchId)
                ->lockForUpdate()
                ->findOrFail($data['invoice_id']);

            $selectedLines = collect($data['lines'])
                ->filter(fn (array $line) => ($line['selected'] ?? true) && (float) $line['quantity'] > 0);

            abort_if($selectedLines->isEmpty(), 422, 'Keep at least one invoice item.');
            $this->validateInvoiceQuantities($invoice, $selectedLines, null);

            $firstLocation = $this->customerLocation($companyId, $branchId, (int) $selectedLines->first()['to_location_id']);

            $transfer = StockTransfer::create([
                'company_id' => $companyId,
                'invoice_id' => $invoice->id,
                'from_branch_id' => $branchId,
                'to_branch_id' => $branchId,
                'from_warehouse_id' => $firstLocation->warehouse_id,
                'to_warehouse_id' => $firstLocation->warehouse_id,
                'from_location_id' => $firstLocation->id,
                'to_location_id' => $firstLocation->id,
                'transfer_no' => DocumentNumber::make(StockTransfer::class, 'transfer_no', 'CS', $branchId),
                'transfer_type' => 'customer_stock_keep',
                'status' => 'draft',
                'transfer_date' => now(),
                'created_by' => $request->user()->id,
                'note' => $data['note'] ?? null,
            ]);

            $this->saveLines($transfer, $invoice, $selectedLines, $companyId, $branchId);

            return $transfer;
        });

        return redirect()
            ->route('stock-customer.index')
            ->with('success', "Customer keep stock {$transfer->transfer_no} saved as draft.");
    }

    public function update(Request $request, StockTransfer $stockTransfer)
    {
        [$companyId, $branchId] = $this->scope($request);
        $this->ensureDraftKeep($stockTransfer, $companyId, $branchId);
        $data = $this->validateKeep($request, $companyId);

        DB::transaction(function () use ($data, $companyId, $branchId, $stockTransfer) {
            $invoice = $this->invoiceQuery($companyId, $branchId)
                ->lockForUpdate()
                ->findOrFail($data['invoice_id']);

            $selectedLines = collect($data['lines'])
                ->filter(fn (array $line) => ($line['selected'] ?? true) && (float) $line['quantity'] > 0);

            abort_if($selectedLines->isEmpty(), 422, 'Keep at least one invoice item.');
            $this->validateInvoiceQuantities($invoice, $selectedLines, $stockTransfer->id);

            $firstLocation = $this->customerLocation($companyId, $branchId, (int) $selectedLines->first()['to_location_id']);

            $stockTransfer->update([
                'invoice_id' => $invoice->id,
                'from_warehouse_id' => $firstLocation->warehouse_id,
                'to_warehouse_id' => $firstLocation->warehouse_id,
                'from_location_id' => $firstLocation->id,
                'to_location_id' => $firstLocation->id,
                'note' => $data['note'] ?? null,
            ]);

            $stockTransfer->lines()->delete();
            $this->saveLines($stockTransfer, $invoice, $selectedLines, $companyId, $branchId);
        });

        return redirect()
            ->route('stock-customer.index')
            ->with('success', "Customer keep stock {$stockTransfer->transfer_no} updated.");
    }

    public function approve(Request $request, StockTransfer $stockTransfer)
    {
        [$companyId, $branchId] = $this->scope($request);
        $this->ensureDraftKeep($stockTransfer, $companyId, $branchId);

        DB::transaction(function () use ($request, $stockTransfer) {
            $stockTransfer->load(['invoice.customer', 'lines.toLocation', 'lines.item']);

            foreach ($stockTransfer->lines as $line) {
                $location = $line->toLocation;
                $quantity = (float) $line->quantity_requested;

                if (! $location || $location->location_type !== 'customer_stock') {
                    throw ValidationException::withMessages(['lines' => 'Customer stock location is no longer available.']);
                }

                $balance = StockBalance::firstOrCreate(
                    [
                        'company_id' => $stockTransfer->company_id,
                        'branch_id' => $stockTransfer->to_branch_id,
                        'warehouse_id' => $location->warehouse_id,
                        'stock_location_id' => $location->id,
                        'item_id' => $line->item_id,
                    ],
                    [
                        'unit_id' => $line->unit_id,
                        'quantity_on_hand' => 0,
                        'quantity_reserved' => 0,
                        'quantity_available' => 0,
                        'average_cost' => $line->unit_cost,
                    ]
                );

                $before = (float) $balance->quantity_available;

                $balance->update([
                    'unit_id' => $line->unit_id,
                    'quantity_on_hand' => (float) $balance->quantity_on_hand + $quantity,
                    'quantity_available' => $before + $quantity,
                    'average_cost' => $line->unit_cost,
                ]);

                $line->update([
                    'quantity_dispatched' => $quantity,
                    'quantity_received' => $quantity,
                    'status' => 'received',
                ]);

                $movement = StockMovement::create([
                    'company_id' => $stockTransfer->company_id,
                    'branch_id' => $stockTransfer->to_branch_id,
                    'warehouse_id' => $location->warehouse_id,
                    'from_location_id' => null,
                    'to_location_id' => $location->id,
                    'item_id' => $line->item_id,
                    'unit_id' => $line->unit_id,
                    'movement_type' => 'customer_stock_keep',
                    'quantity' => $quantity,
                    'unit_cost' => $line->unit_cost,
                    'total_cost' => $quantity * (float) $line->unit_cost,
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $stockTransfer->id,
                    'reference_no' => $stockTransfer->transfer_no,
                    'movement_date' => $stockTransfer->transfer_date ?? now(),
                    'created_by' => $request->user()->id,
                    'note' => trim("Invoice: {$stockTransfer->invoice?->invoice_no}\n".$stockTransfer->note),
                ]);

                StockLog::create([
                    'company_id' => $stockTransfer->company_id,
                    'branch_id' => $stockTransfer->to_branch_id,
                    'stock_movement_id' => $movement->id,
                    'item_id' => $line->item_id,
                    'action' => 'customer_stock_keep',
                    'quantity_before' => $before,
                    'quantity_after' => $before + $quantity,
                    'quantity_changed' => $quantity,
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $stockTransfer->id,
                    'reference_no' => $stockTransfer->transfer_no,
                    'performed_by' => $request->user()->id,
                    'note' => $stockTransfer->note,
                ]);
            }

            $stockTransfer->update([
                'status' => 'received',
                'approved_at' => now(),
                'dispatched_at' => now(),
                'received_at' => now(),
                'approved_by' => $request->user()->id,
                'dispatched_by' => $request->user()->id,
                'received_by' => $request->user()->id,
            ]);
        });

        return back()->with('success', "Customer keep stock {$stockTransfer->transfer_no} approved.");
    }

    public function reject(Request $request, StockTransfer $stockTransfer)
    {
        [$companyId, $branchId] = $this->scope($request);
        $this->ensureDraftKeep($stockTransfer, $companyId, $branchId);

        $stockTransfer->update([
            'status' => 'rejected',
            'cancelled_at' => now(),
            'cancelled_by' => $request->user()->id,
            'cancel_reason' => 'Rejected by approver',
        ]);

        return back()->with('success', "Customer keep stock {$stockTransfer->transfer_no} rejected.");
    }

    public function cancel(Request $request, StockTransfer $stockTransfer)
    {
        [$companyId, $branchId] = $this->scope($request);
        $this->ensureDraftKeep($stockTransfer, $companyId, $branchId);

        $stockTransfer->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $request->user()->id,
            'cancel_reason' => 'Cancelled by user',
        ]);

        return back()->with('success', "Customer keep stock {$stockTransfer->transfer_no} cancelled.");
    }

    public function item(Request $request, Item $item): Response
    {
        [$companyId, $branchId] = $this->scope($request);

        abort_if((int) $item->company_id !== (int) $companyId, 403);
        $item->load('unit:id,name,code');

        $rows = StockTransferLine::query()
            ->with([
                'stockTransfer.invoice.customer:id,name,phone_number',
                'stockTransfer.toLocation:id,name,code',
                'unit:id,name,code',
                'toLocation:id,name,code',
            ])
            ->where('item_id', $item->id)
            ->whereHas('stockTransfer', fn ($query) => $query
                ->where('company_id', $companyId)
                ->where('from_branch_id', $branchId)
                ->where('transfer_type', 'customer_stock_keep')
                ->where('status', 'received'))
            ->latest()
            ->get()
            ->map(fn (StockTransferLine $line): array => [
                'id' => $line->id,
                'date' => $line->stockTransfer?->received_at?->format('M d, Y') ?? $line->created_at?->format('M d, Y'),
                'documentNo' => $line->stockTransfer?->transfer_no ?? '-',
                'invoiceNo' => $line->stockTransfer?->invoice?->invoice_no ?? '-',
                'customer' => $line->stockTransfer?->invoice?->customer?->name ?? 'Walk-in Customer',
                'phone' => $line->stockTransfer?->invoice?->customer?->phone_number,
                'location' => $line->toLocation?->code ?? $line->toLocation?->name ?? 'Customer Stock',
                'quantity' => (float) $line->quantity_received,
                'unit' => $line->unit?->code ?? $line->unit?->name ?? 'Unit',
                'note' => $line->stockTransfer?->note,
            ]);

        return Inertia::render('StockCustomer/View', [
            'item' => [
                'id' => $item->id,
                'code' => $item->code ?: 'ITEM-'.str_pad((string) $item->id, 4, '0', STR_PAD_LEFT),
                'name' => $item->name,
                'unit' => $item->unit?->code ?? $item->unit?->name ?? 'Unit',
                'totalQuantity' => $rows->sum('quantity'),
            ],
            'keeps' => $rows,
        ]);
    }

    private function validateKeep(Request $request, int $companyId): array
    {
        return $request->validate([
            'invoice_id' => ['required', Rule::exists('invoices', 'id')->where('company_id', $companyId)],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['required', Rule::exists('items', 'id')->where('company_id', $companyId)],
            'lines.*.unit_id' => ['required', 'integer'],
            'lines.*.to_location_id' => ['required', Rule::exists('stock_locations', 'id')->where('company_id', $companyId)->where('location_type', 'customer_stock')],
            'lines.*.quantity' => ['required', 'numeric', 'min:0'],
            'lines.*.selected' => ['boolean'],
        ]);
    }

    private function saveLines(StockTransfer $transfer, Invoice $invoice, Collection $selectedLines, int $companyId, int $branchId): void
    {
        $invoiceLines = $this->invoiceItemLines($invoice)->keyBy('item_id');

        foreach ($selectedLines as $line) {
            $invoiceLine = $invoiceLines->get((int) $line['item_id']);
            $location = $this->customerLocation($companyId, $branchId, (int) $line['to_location_id']);
            $quantity = (float) $line['quantity'];
            $unitCost = (float) ($invoiceLine['unit_cost'] ?? 0);

            StockTransferLine::create([
                'stock_transfer_id' => $transfer->id,
                'item_id' => $invoiceLine['item_id'],
                'unit_id' => $invoiceLine['unit_id'],
                'to_location_id' => $location->id,
                'quantity_requested' => $quantity,
                'quantity_dispatched' => 0,
                'quantity_received' => 0,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'status' => 'open',
            ]);
        }
    }

    private function validateInvoiceQuantities(Invoice $invoice, Collection $selectedLines, ?int $exceptTransferId): void
    {
        $invoiceLines = $this->invoiceItemLines($invoice)->keyBy('item_id');
        $alreadyKept = $this->keptQuantities($invoice, $exceptTransferId);

        foreach ($selectedLines->groupBy('item_id') as $itemId => $lines) {
            $invoiceLine = $invoiceLines->get((int) $itemId);
            $requested = $lines->sum(fn (array $line) => (float) $line['quantity']);
            $remaining = (float) ($invoiceLine['quantity_invoiced'] ?? 0) - (float) ($alreadyKept[$itemId] ?? 0);

            if (! $invoiceLine || $requested > $remaining) {
                throw ValidationException::withMessages(['lines' => 'Customer keep quantity is greater than the selected invoice quantity.']);
            }
        }
    }

    private function invoiceQuery(int $companyId, int $branchId)
    {
        return Invoice::query()
            ->with(['customer:id,name,phone_number', 'lines.menu.item.unit'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('status', '!=', 'cancelled');
    }

    private function formattedInvoices(int $companyId, int $branchId): Collection
    {
        return $this->invoiceQuery($companyId, $branchId)
            ->latest('issued_at')
            ->get()
            ->map(fn (Invoice $invoice) => $this->formatInvoice($invoice))
            ->values();
    }

    private function formatInvoice(Invoice $invoice, ?int $exceptTransferId = null): array
    {
        $kept = $this->keptQuantities($invoice, $exceptTransferId);
        $lines = $this->invoiceItemLines($invoice)
            ->map(function (array $line) use ($kept): array {
                $keptQuantity = (float) ($kept[$line['item_id']] ?? 0);

                return [
                    ...$line,
                    'quantity_kept' => $keptQuantity,
                    'quantity_remaining' => max(0, (float) $line['quantity_invoiced'] - $keptQuantity),
                ];
            })
            ->values();

        return [
            'id' => $invoice->id,
            'invoice_no' => $invoice->invoice_no,
            'issued_at' => $invoice->issued_at?->format('M d, Y'),
            'customer_name' => $invoice->customer?->name ?? 'Walk-in Customer',
            'customer_phone' => $invoice->customer?->phone_number,
            'line_count' => $lines->count(),
            'total_invoiced' => $lines->sum('quantity_invoiced'),
            'total_remaining' => $lines->sum('quantity_remaining'),
            'progress' => $lines->sum('quantity_invoiced') > 0
                ? round((($lines->sum('quantity_invoiced') - $lines->sum('quantity_remaining')) / $lines->sum('quantity_invoiced')) * 100)
                : 0,
            'lines' => $lines,
        ];
    }

    private function invoiceItemLines(Invoice $invoice): Collection
    {
        return $invoice->lines
            ->filter(fn (InvoiceLine $line) => $line->menu?->item?->is_stockable)
            ->groupBy(fn (InvoiceLine $line) => $line->menu->item->id)
            ->map(function (Collection $lines, int $itemId): array {
                $first = $lines->first();
                $item = $first->menu->item;

                return [
                    'item_id' => $itemId,
                    'unit_id' => $item->unit_id,
                    'item_name' => $item->name,
                    'item_code' => $item->code,
                    'unit_code' => $item->unit?->code ?? $item->unit?->name ?? 'Unit',
                    'quantity_invoiced' => $lines->sum(fn (InvoiceLine $line) => (float) $line->quantity),
                    'unit_cost' => (float) $item->cost,
                ];
            })
            ->values();
    }

    private function keptQuantities(Invoice $invoice, ?int $exceptTransferId = null): array
    {
        return StockTransferLine::query()
            ->whereHas('stockTransfer', fn ($query) => $query
                ->where('invoice_id', $invoice->id)
                ->where('transfer_type', 'customer_stock_keep')
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->when($exceptTransferId, fn ($query) => $query->whereKeyNot($exceptTransferId)))
            ->selectRaw('item_id, sum(quantity_requested) as quantity')
            ->groupBy('item_id')
            ->pluck('quantity', 'item_id')
            ->map(fn ($quantity) => (float) $quantity)
            ->all();
    }

    private function customerLocations(int $companyId, int $branchId): Collection
    {
        return StockLocation::query()
            ->with('warehouse:id,name,code')
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('location_type', 'customer_stock')
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(fn (StockLocation $location): array => [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'warehouse_name' => $location->warehouse?->name,
            ]);
    }

    private function customerLocation(int $companyId, int $branchId, int $locationId): StockLocation
    {
        return StockLocation::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('location_type', 'customer_stock')
            ->findOrFail($locationId);
    }

    private function ensureDraftKeep(StockTransfer $stockTransfer, int $companyId, int $branchId): void
    {
        abort_if((int) $stockTransfer->company_id !== (int) $companyId, 403);
        abort_if((int) $stockTransfer->from_branch_id !== (int) $branchId, 403);
        abort_if($stockTransfer->transfer_type !== 'customer_stock_keep', 403);
        abort_if($stockTransfer->status !== 'draft', 422, 'Only draft customer keep stock can be changed.');
    }

    private function formatEditableTransfer(StockTransfer $transfer): array
    {
        return [
            'id' => $transfer->id,
            'transfer_no' => $transfer->transfer_no,
            'invoice_id' => $transfer->invoice_id,
            'note' => $transfer->note,
            'lines' => $transfer->lines->map(fn (StockTransferLine $line) => [
                'allocation_key' => "existing-{$line->id}",
                'item_id' => $line->item_id,
                'unit_id' => $line->unit_id,
                'to_location_id' => $line->to_location_id,
                'quantity' => (float) $line->quantity_requested,
                'selected' => true,
            ]),
        ];
    }

    private function formatTransfer(StockTransfer $transfer): array
    {
        return [
            'id' => $transfer->id,
            'transfer_no' => $transfer->transfer_no,
            'created_at' => $transfer->created_at?->format('M d, Y H:i'),
            'updated_at' => $transfer->updated_at?->format('M d, Y H:i'),
            'approved_at' => $transfer->approved_at?->format('M d, Y H:i'),
            'cancelled_at' => $transfer->cancelled_at?->format('M d, Y H:i'),
            'invoice_no' => $transfer->invoice?->invoice_no,
            'customer_name' => $transfer->invoice?->customer?->name ?? 'Walk-in Customer',
            'customer_phone' => $transfer->invoice?->customer?->phone_number,
            'assigned_staff' => $transfer->creator?->name ?? 'Unassigned',
            'created_by' => $transfer->creator?->name,
            'approved_by' => $transfer->approver?->name,
            'cancelled_by' => $transfer->canceller?->name,
            'item_count' => $transfer->lines->count(),
            'total_quantity' => $transfer->lines->sum(fn (StockTransferLine $line) => (float) ($transfer->status === 'received' ? $line->quantity_received : $line->quantity_requested)),
            'status' => $transfer->status,
            'lines' => $transfer->lines->map(fn (StockTransferLine $line) => [
                'id' => $line->id,
                'item_name' => $line->item?->name,
                'item_code' => $line->item?->code,
                'unit_code' => $line->unit?->code,
                'to_location' => $line->toLocation?->code ?? $line->toLocation?->name,
                'quantity' => (float) ($transfer->status === 'received' ? $line->quantity_received : $line->quantity_requested),
                'unit_cost' => (float) $line->unit_cost,
                'total_cost' => (float) $line->total_cost,
            ]),
        ];
    }

    private function scope(Request $request): array
    {
        $user = $request->user();
        $companyId = $user->company_id ?? Company::query()->value('id');
        $branchId = $user->branch_id ?? Branch::query()->where('company_id', $companyId)->value('id');

        abort_if(! $companyId, 422, 'No company is available for customer stock.');
        abort_if(! $branchId, 422, 'No branch is available for customer stock.');

        return [$companyId, $branchId];
    }
}
