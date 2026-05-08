<?php

namespace App\Http\Controllers\GoodsReceipt;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptLine;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class GoodsReceiptController extends Controller
{
    public function index(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $receipts = GoodsReceipt::query()
            ->with(['purchaseOrder', 'stockLocation', 'receiver', 'lines.item', 'lines.unit', 'lines.stockLocation'])
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (GoodsReceipt $receipt) => $this->formatReceipt($receipt));

        $waitingPurchaseOrders = $this->formattedReceivablePurchaseOrders($companyId, $branchId)->count();

        return Inertia::render('GoodsReceipt/Index', [
            'receipts' => $receipts,
            'stats' => [
                'waitingPurchaseOrders' => $waitingPurchaseOrders,
                'awaitingStaging' => $receipts->whereIn('status', ['draft', 'in_progress'])->count(),
                'readyForPutaway' => $receipts->whereIn('status', ['approved', 'partially_received', 'received'])->count(),
            ],
        ]);
    }

    public function create(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $purchaseOrder = null;
        if ($request->integer('purchase_order_id')) {
            $purchaseOrder = $this->receivablePurchaseOrders($companyId, $branchId)
                ->whereKey($request->integer('purchase_order_id'))
                ->first();
        }

        return Inertia::render('GoodsReceipt/CreateGoodsReceipt', [
            'nextReceiptNo' => DocumentNumber::make(GoodsReceipt::class, 'receipt_no', 'GR'),
            'purchaseOrder' => $purchaseOrder ? $this->formatPurchaseOrder($purchaseOrder) : null,
            'purchaseOrders' => $this->formattedReceivablePurchaseOrders($companyId, $branchId),
            'stagingLocations' => $this->stagingLocations($companyId, $branchId),
        ]);
    }

    public function store(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $request->validate([
            'purchase_order_id' => [
                'required',
                Rule::exists('purchase_orders', 'id')
                    ->where('company_id', $companyId),
            ],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.purchase_order_line_id' => ['required', 'integer'],
            'lines.*.stock_location_id' => [
                'required',
                Rule::exists('stock_locations', 'id')
                    ->where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->where('location_type', 'inbound_staging'),
            ],
            'lines.*.quantity_received' => ['required', 'numeric', 'min:0'],
        ]);

        $receipt = DB::transaction(function () use ($request, $data, $companyId, $branchId) {
            $purchaseOrder = $this->receivablePurchaseOrders($companyId, $branchId)
                ->lockForUpdate()
                ->findOrFail($data['purchase_order_id']);

            $purchaseOrder->load(['lines.item', 'lines.unit']);

            $lineInput = collect($data['lines'])
                ->filter(fn (array $line) => (float) $line['quantity_received'] > 0);

            abort_if($lineInput->isEmpty(), 422, 'Receive at least one item quantity.');

            $firstLocationId = (int) $lineInput->first()['stock_location_id'];
            $location = StockLocation::query()
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('location_type', 'inbound_staging')
                ->findOrFail($firstLocationId);

            $lineQuantities = $lineInput
                ->groupBy('purchase_order_line_id')
                ->map(fn ($lines) => $lines->sum(fn (array $line) => (float) $line['quantity_received']));

            $purchaseOrderLineIds = $purchaseOrder->lines->pluck('id')->map(fn ($id) => (int) $id);
            $unknownLineIds = $lineQuantities->keys()
                ->map(fn ($id) => (int) $id)
                ->diff($purchaseOrderLineIds);

            abort_if($unknownLineIds->isNotEmpty(), 422, 'Receipt lines must belong to the selected purchase order.');

            $receivableLines = $purchaseOrder->lines
                ->filter(fn (PurchaseOrderLine $line) => $lineQuantities->has($line->id) && $lineQuantities[$line->id] > 0);

            abort_if($receivableLines->isEmpty(), 422, 'Receive at least one item quantity.');

            foreach ($receivableLines as $line) {
                $quantity = $lineQuantities[$line->id];
                $activeReceived = $this->activeGoodsReceiptQuantities($purchaseOrder)->get($line->id, 0);
                $activeRemaining = max(0, (float) $line->quantity_ordered - (float) $activeReceived);

                abort_if($quantity > $activeRemaining, 422, "Received quantity for {$line->item?->name} is greater than the remaining PO quantity.");
            }

            $receipt = GoodsReceipt::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'purchase_order_id' => $purchaseOrder->id,
                'warehouse_id' => $location->warehouse_id,
                'stock_location_id' => $location->id,
                'receipt_no' => DocumentNumber::make(GoodsReceipt::class, 'receipt_no', 'GR'),
                'status' => 'draft',
                'received_at' => null,
                'received_by' => $request->user()->id,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($lineInput as $inputLine) {
                $line = $purchaseOrder->lines->firstWhere('id', (int) $inputLine['purchase_order_line_id']);
                $quantity = (float) $inputLine['quantity_received'];
                $totalCost = $quantity * (float) $line->unit_cost;

                GoodsReceiptLine::create([
                    'goods_receipt_id' => $receipt->id,
                    'purchase_order_line_id' => $line->id,
                    'item_id' => $line->item_id,
                    'unit_id' => $line->unit_id,
                    'stock_location_id' => $inputLine['stock_location_id'],
                    'quantity_received' => $quantity,
                    'unit_cost' => $line->unit_cost,
                    'total_cost' => $totalCost,
                ]);
            }

            return $receipt;
        });

        return redirect()
            ->route('goods-receipts.index')
            ->with('success', "Goods receipt {$receipt->receipt_no} saved as draft.");
    }

    public function approvedPurchaseOrders(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        return Inertia::render('GoodsReceipt/ApprovedPO_forGR', [
            'orders' => $this->formattedReceivablePurchaseOrders($companyId, $branchId),
        ]);
    }

    public function approve(Request $request, GoodsReceipt $goodsReceipt)
    {
        $this->ensureDraftReceipt($request, $goodsReceipt);

        $goodsReceipt->load(['lines.stockLocation', 'stockLocation']);

        DB::transaction(function () use ($request, $goodsReceipt) {
            foreach ($goodsReceipt->lines as $line) {
                $quantity = (float) $line->quantity_received;

                if ($quantity <= 0) {
                    continue;
                }

                $balance = StockBalance::firstOrCreate(
                    [
                        'company_id' => $goodsReceipt->company_id,
                        'branch_id' => $goodsReceipt->branch_id,
                        'warehouse_id' => $line->stockLocation?->warehouse_id ?? $goodsReceipt->warehouse_id,
                        'stock_location_id' => $line->stock_location_id ?? $goodsReceipt->stock_location_id,
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

                $balance->update([
                    'quantity_on_hand' => (float) $balance->quantity_on_hand + $quantity,
                    'quantity_available' => (float) $balance->quantity_available + $quantity,
                    'average_cost' => $line->unit_cost,
                ]);

                StockMovement::create([
                    'company_id' => $goodsReceipt->company_id,
                    'branch_id' => $goodsReceipt->branch_id,
                    'warehouse_id' => $line->stockLocation?->warehouse_id ?? $goodsReceipt->warehouse_id,
                    'from_location_id' => null,
                    'to_location_id' => $line->stock_location_id ?? $goodsReceipt->stock_location_id,
                    'item_id' => $line->item_id,
                    'unit_id' => $line->unit_id,
                    'movement_type' => 'purchase_receipt',
                    'quantity' => $quantity,
                    'unit_cost' => $line->unit_cost,
                    'total_cost' => $quantity * (float) $line->unit_cost,
                    'reference_type' => 'goods_receipt',
                    'reference_id' => $goodsReceipt->id,
                    'reference_no' => $goodsReceipt->receipt_no,
                    'movement_date' => now(),
                    'created_by' => $request->user()->id,
                    'note' => 'Approved goods receipt into staging',
                ]);
            }

            $goodsReceipt->update([
                'status' => 'approved',
                'received_at' => now(),
            ]);
        });

        return back()->with('success', "Goods receipt {$goodsReceipt->receipt_no} approved.");
    }

    public function reject(Request $request, GoodsReceipt $goodsReceipt)
    {
        $this->ensureDraftReceipt($request, $goodsReceipt);

        $goodsReceipt->update(['status' => 'rejected']);

        return back()->with('success', "Goods receipt {$goodsReceipt->receipt_no} rejected.");
    }

    public function cancel(Request $request, GoodsReceipt $goodsReceipt)
    {
        $this->ensureDraftReceipt($request, $goodsReceipt);

        $goodsReceipt->update([
            'status' => 'cancelled',
            'cancelled_by' => $request->user()->id,
        ]);

        return back()->with('success', "Goods receipt {$goodsReceipt->receipt_no} cancelled.");
    }

    private function scope(Request $request): array
    {
        $user = $request->user();
        $companyId = $user->company_id ?? Company::query()->value('id');
        $branchId = $user->branch_id ?? Branch::query()->where('company_id', $companyId)->value('id');

        abort_if(! $companyId, 422, 'No company is available for goods receipts.');
        abort_if(! $branchId, 422, 'No branch is available for goods receipts.');

        return [$companyId, $branchId];
    }

    private function ensureDraftReceipt(Request $request, GoodsReceipt $goodsReceipt): void
    {
        [$companyId, $branchId] = $this->scope($request);

        abort_if((int) $goodsReceipt->company_id !== (int) $companyId, 403);
        abort_if((int) $goodsReceipt->branch_id !== (int) $branchId, 403);
        abort_if($goodsReceipt->status !== 'draft', 422, 'Only draft goods receipts can be changed.');
    }

    private function receivablePurchaseOrders(int $companyId, ?int $branchId)
    {
        return PurchaseOrder::query()
            ->with(['lines.item', 'lines.unit'])
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where(function ($inner) use ($branchId) {
                $inner->whereNull('branch_id')->orWhere('branch_id', $branchId);
            }))
            ->whereIn('status', ['approved', 'in_progress_receipt', 'partially_received'])
            ->whereHas('lines', fn ($query) => $query->where('quantity_remaining', '>', 0));
    }

    private function activeGoodsReceiptQuantities(PurchaseOrder $order)
    {
        return GoodsReceiptLine::query()
            ->whereHas('goodsReceipt', function ($query) use ($order) {
                $query->where('purchase_order_id', $order->id)
                    ->whereNotIn('status', ['rejected', 'cancelled']);
            })
            ->selectRaw('purchase_order_line_id, sum(quantity_received) as quantity')
            ->groupBy('purchase_order_line_id')
            ->pluck('quantity', 'purchase_order_line_id')
            ->map(fn ($quantity) => (float) $quantity);
    }

    private function formattedReceivablePurchaseOrders(int $companyId, ?int $branchId)
    {
        return $this->receivablePurchaseOrders($companyId, $branchId)
            ->latest('approved_at')
            ->latest()
            ->get()
            ->map(fn (PurchaseOrder $order) => $this->formatPurchaseOrder($order))
            ->filter(fn (array $order) => $order['remaining_quantity'] > 0)
            ->values();
    }

    private function stagingLocations(int $companyId, ?int $branchId)
    {
        return StockLocation::query()
            ->with('warehouse')
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->where('is_active', true)
            ->where('location_type', 'inbound_staging')
            ->orderBy('name')
            ->get()
            ->map(fn (StockLocation $location) => [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'location_type' => $location->location_type,
                'warehouse_id' => $location->warehouse_id,
                'warehouse_name' => $location->warehouse?->name,
            ]);
    }

    private function formatReceipt(GoodsReceipt $receipt): array
    {
        return [
            'id' => $receipt->id,
            'receipt_no' => $receipt->receipt_no,
            'purchase_order_no' => $receipt->purchaseOrder?->po_no,
            'status' => $receipt->status,
            'created_at' => $receipt->created_at?->format('M d, Y h:i A'),
            'received_at' => $receipt->received_at?->format('M d, Y h:i A'),
            'staging_area' => $receipt->stockLocation?->code ?? $receipt->stockLocation?->name,
            'operator' => $receipt->receiver?->name,
            'line_count' => $receipt->lines->count(),
            'lines' => $receipt->lines->map(fn (GoodsReceiptLine $line) => [
                'id' => $line->id,
                'item_name' => $line->item?->name,
                'item_code' => $line->item?->code,
                'unit_code' => $line->unit?->code,
                'staging_area' => $line->stockLocation?->code ?? $line->stockLocation?->name,
                'quantity_received' => (float) $line->quantity_received,
            ]),
        ];
    }

    private function formatPurchaseOrder(PurchaseOrder $order): array
    {
        $ordered = $order->lines->sum(fn (PurchaseOrderLine $line) => (float) $line->quantity_ordered);
        $activeReceivedByLine = $this->activeGoodsReceiptQuantities($order);
        $received = $order->lines->sum(fn (PurchaseOrderLine $line) => (float) $activeReceivedByLine->get($line->id, 0));
        $remaining = $order->lines->sum(fn (PurchaseOrderLine $line) => max(0, (float) $line->quantity_ordered - (float) $activeReceivedByLine->get($line->id, 0)));
        $progress = $ordered > 0 ? round(($received / $ordered) * 100) : 0;

        return [
            'id' => $order->id,
            'po_no' => $order->po_no,
            'supplier_name' => $order->supplier_name,
            'supplier_phone' => $order->supplier_phone,
            'supplier_address' => $order->supplier_address,
            'status' => $order->status,
            'receipt_status' => $this->receiptStatus($received, $remaining),
            'order_date' => $order->order_date?->format('M d, Y'),
            'expected_date' => $order->expected_date?->format('M d, Y'),
            'ordered_quantity' => $ordered,
            'received_quantity' => $received,
            'remaining_quantity' => $remaining,
            'progress' => $progress,
            'lines' => $order->lines->map(fn (PurchaseOrderLine $line) => [
                'id' => $line->id,
                'item_name' => $line->item?->name,
                'item_code' => $line->item?->code,
                'unit_code' => $line->unit?->code,
                'quantity_ordered' => (float) $line->quantity_ordered,
                'quantity_received' => (float) $activeReceivedByLine->get($line->id, 0),
                'quantity_remaining' => max(0, (float) $line->quantity_ordered - (float) $activeReceivedByLine->get($line->id, 0)),
                'unit_cost' => (float) $line->unit_cost,
            ]),
        ];
    }

    private function receiptStatus(float $received, float $remaining): string
    {
        if ($received <= 0) {
            return 'Ready to Receive';
        }

        if ($remaining > 0) {
            return 'Partially Receiving';
        }

        return 'Fully Received';
    }
}
