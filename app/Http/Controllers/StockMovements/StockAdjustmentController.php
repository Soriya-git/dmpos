<?php

namespace App\Http\Controllers\StockMovements;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentLine;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockLog;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StockAdjustmentController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('StockAdjustment/Index', $this->pageProps($request));
    }

    public function create(Request $request): Response
    {
        return Inertia::render('StockAdjustment/Create', $this->pageProps($request));
    }

    public function store(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $request->validate([
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'adjustment_type' => ['required', Rule::in(['adjustment_in', 'adjustment_out'])],
            'adjustment_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['required', Rule::exists('items', 'id')->where('company_id', $companyId)],
            'lines.*.stock_location_id' => ['exclude_unless:adjustment_type,adjustment_in', 'required', Rule::exists('stock_locations', 'id')->where('company_id', $companyId)],
            'lines.*.stock_balance_id' => ['exclude_unless:adjustment_type,adjustment_out', 'required', Rule::exists('stock_balances', 'id')->where('company_id', $companyId)],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'lines.*.note' => ['nullable', 'string'],
        ]);

        $adjustment = DB::transaction(function () use ($request, $data, $companyId, $branchId): StockAdjustment {
            $warehouse = Warehouse::query()
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->findOrFail($data['warehouse_id']);

            if ($data['adjustment_type'] === 'adjustment_in') {
                return $this->storeAdjustmentIn($request, $data, $companyId, $branchId, $warehouse);
            }

            return $this->storeAdjustmentOut($request, $data, $companyId, $branchId, $warehouse);
        });

        return redirect()
            ->route('stock-movements.stock-adjustments')
            ->with('success', "Stock adjustment {$adjustment->adjustment_no} saved as draft.");
    }

    public function approve(Request $request, StockAdjustment $stockAdjustment)
    {
        $this->ensureDraftAdjustment($request, $stockAdjustment);

        DB::transaction(function () use ($request, $stockAdjustment) {
            $stockAdjustment->load('lines');

            foreach ($stockAdjustment->lines as $line) {
                $quantity = abs((float) $line->difference_quantity);

                if ($quantity <= 0) {
                    continue;
                }

                if ($stockAdjustment->adjustment_type === 'adjustment_in') {
                    $this->approveLineIn($request, $stockAdjustment, $line, $quantity);

                    continue;
                }

                $this->approveLineOut($request, $stockAdjustment, $line, $quantity);
            }

            $stockAdjustment->update([
                'status' => 'confirmed',
                'confirmed_by' => $request->user()->id,
                'confirmed_at' => now(),
            ]);
        });

        return back()->with('success', "Stock adjustment {$stockAdjustment->adjustment_no} approved.");
    }

    public function reject(Request $request, StockAdjustment $stockAdjustment)
    {
        $this->ensureDraftAdjustment($request, $stockAdjustment);

        $stockAdjustment->update([
            'status' => 'cancelled',
            'cancelled_by' => $request->user()->id,
            'cancelled_at' => now(),
            'cancel_reason' => 'Rejected by approver',
        ]);

        return back()->with('success', "Stock adjustment {$stockAdjustment->adjustment_no} rejected.");
    }

    public function cancel(Request $request, StockAdjustment $stockAdjustment)
    {
        $this->ensureDraftAdjustment($request, $stockAdjustment);

        $stockAdjustment->update([
            'status' => 'cancelled',
            'cancelled_by' => $request->user()->id,
            'cancelled_at' => now(),
            'cancel_reason' => 'Cancelled by user',
        ]);

        return back()->with('success', "Stock adjustment {$stockAdjustment->adjustment_no} cancelled.");
    }

    private function pageProps(Request $request): array
    {
        [$companyId, $branchId] = $this->scope($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:50'],
            'location_id' => ['nullable', 'integer'],
        ]);

        $inventory = StockBalance::query()
            ->with([
                'branch:id,name,code',
                'warehouse:id,name,code',
                'stockLocation:id,name,code,location_type',
                'item:id,name,code,item_type,cost',
                'unit:id,name,code',
            ])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('quantity_available', '>', 0)
            ->whereHas('stockLocation', fn ($query) => $query->where('is_active', true))
            ->whereHas('item', fn ($query) => $query->where('is_active', true)->where('is_stockable', true))
            ->orderBy('warehouse_id')
            ->orderBy('stock_location_id')
            ->orderBy('item_id')
            ->get()
            ->map(fn (StockBalance $balance): array => $this->formatBalance($balance));

        $locations = StockLocation::query()
            ->with(['warehouse:id,name,code', 'branch:id,name,code'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->where('location_type', 'putaway')
            ->orderBy('warehouse_id')
            ->orderBy('code')
            ->get()
            ->map(fn (StockLocation $location): array => [
                'id' => $location->id,
                'warehouseId' => $location->warehouse_id,
                'code' => $location->code ?? 'LOC',
                'name' => $location->name,
                'warehouse' => $location->warehouse?->name ?? 'Warehouse',
                'branch' => $location->branch?->name ?? 'Unassigned',
                'type' => $location->location_type,
            ]);

        $warehouses = Warehouse::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Warehouse $warehouse): array => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'branch' => $warehouse->branch?->name ?? 'Unassigned',
            ]);

        $items = Item::query()
            ->with('unit:id,name,code')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('is_stockable', true)
            ->where(function ($query) use ($branchId) {
                $query->whereNull('branch_id')->orWhere('branch_id', $branchId);
            })
            ->orderBy('name')
            ->get()
            ->map(fn (Item $item): array => [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code ?: 'ITEM-'.str_pad((string) $item->id, 4, '0', STR_PAD_LEFT),
                'unitCode' => $item->unit?->code ?? $item->unit?->name ?? 'Unit',
                'cost' => (float) $item->cost,
                'hasStock' => $inventory->contains('itemId', $item->id),
            ]);

        $adjustments = StockAdjustment::query()
            ->with([
                'branch:id,name,code',
                'warehouse:id,name,code',
                'stockLocation:id,name,code',
                'creator:id,name',
                'lines.item:id,name,code',
                'lines.unit:id,name,code',
                'lines.stockLocation:id,name,code',
            ])
            ->withCount('lines')
            ->whereIn('adjustment_type', ['adjustment_in', 'adjustment_out'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('adjustment_type', $type))
            ->when($filters['location_id'] ?? null, fn ($query, $locationId) => $query->where(function ($inner) use ($locationId) {
                $inner->where('stock_location_id', $locationId)
                    ->orWhereHas('lines', fn ($lineQuery) => $lineQuery->where('stock_location_id', $locationId));
            }))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $this->statusFilter($status)))
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('adjustment_no', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhereHas('lines.item', fn ($lineQuery) => $lineQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->latest('adjustment_date')
            ->latest()
            ->get()
            ->map(fn (StockAdjustment $adjustment): array => $this->formatAdjustment($adjustment));

        return [
            'adjustments' => $adjustments,
            'inventory' => $inventory,
            'locations' => $locations,
            'warehouses' => $warehouses,
            'items' => $items,
            'nextAdjustmentNo' => DocumentNumber::make(StockAdjustment::class, 'adjustment_no', 'SA'),
            'filters' => [
                'search' => $filters['search'] ?? null,
                'status' => $filters['status'] ?? null,
                'type' => $filters['type'] ?? null,
                'location_id' => $filters['location_id'] ?? null,
            ],
        ];
    }

    private function storeAdjustmentIn(Request $request, array $data, int $companyId, int $branchId, Warehouse $warehouse): StockAdjustment
    {
        $locationIds = collect($data['lines'])->pluck('stock_location_id')->unique()->values();
        $locations = StockLocation::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('warehouse_id', $warehouse->id)
            ->where('is_active', true)
            ->where('location_type', 'putaway')
            ->whereIn('id', $locationIds)
            ->get()
            ->keyBy('id');

        $items = Item::query()
            ->with('unit:id,name,code')
            ->where('company_id', $companyId)
            ->whereIn('id', collect($data['lines'])->pluck('item_id')->unique())
            ->get()
            ->keyBy('id');

        foreach ($data['lines'] as $line) {
            if (! $locations->has((int) $line['stock_location_id'])) {
                throw ValidationException::withMessages(['lines' => 'Select a putaway location in the selected warehouse for each adjustment-in line.']);
            }
        }

        $firstLocation = $locations->get((int) $data['lines'][0]['stock_location_id']);
        $adjustment = $this->createAdjustment($request, $data, $companyId, $branchId, $warehouse, $firstLocation->id);

        foreach ($data['lines'] as $line) {
            $item = $items->get((int) $line['item_id']);
            $location = $locations->get((int) $line['stock_location_id']);
            $current = StockBalance::query()
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('warehouse_id', $warehouse->id)
                ->where('stock_location_id', $location->id)
                ->where('item_id', $item->id)
                ->first();
            $quantity = (float) $line['quantity'];
            $unitCost = (float) ($line['unit_cost'] ?? $item->cost ?? $current?->average_cost ?? 0);
            $systemQuantity = (float) ($current?->quantity_available ?? 0);

            StockAdjustmentLine::create([
                'stock_adjustment_id' => $adjustment->id,
                'item_id' => $item->id,
                'unit_id' => $item->unit_id,
                'stock_location_id' => $location->id,
                'system_quantity' => $systemQuantity,
                'adjusted_quantity' => $systemQuantity + $quantity,
                'difference_quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'reason' => $data['reason'],
                'note' => $line['note'] ?? null,
            ]);
        }

        return $adjustment;
    }

    private function storeAdjustmentOut(Request $request, array $data, int $companyId, int $branchId, Warehouse $warehouse): StockAdjustment
    {
        $balances = StockBalance::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('warehouse_id', $warehouse->id)
            ->whereIn('id', collect($data['lines'])->pluck('stock_balance_id')->unique())
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach (collect($data['lines'])->groupBy('stock_balance_id') as $balanceId => $lines) {
            $balance = $balances->get((int) $balanceId);
            $quantity = $lines->sum(fn (array $line) => (float) $line['quantity']);

            if (! $balance || $lines->contains(fn (array $line) => (int) $line['item_id'] !== (int) $balance->item_id)) {
                throw ValidationException::withMessages(['lines' => 'Selected location does not match the item.']);
            }

            if ((float) $balance->quantity_available < $quantity) {
                throw ValidationException::withMessages(['lines' => 'Adjustment-out quantity is greater than available stock.']);
            }
        }

        $firstBalance = $balances->get((int) $data['lines'][0]['stock_balance_id']);
        $adjustment = $this->createAdjustment($request, $data, $companyId, $branchId, $warehouse, $firstBalance->stock_location_id);

        foreach ($data['lines'] as $line) {
            $balance = $balances->get((int) $line['stock_balance_id']);
            $quantity = (float) $line['quantity'];

            StockAdjustmentLine::create([
                'stock_adjustment_id' => $adjustment->id,
                'item_id' => $balance->item_id,
                'unit_id' => $balance->unit_id,
                'stock_location_id' => $balance->stock_location_id,
                'system_quantity' => $balance->quantity_available,
                'adjusted_quantity' => (float) $balance->quantity_available - $quantity,
                'difference_quantity' => -$quantity,
                'unit_cost' => $balance->average_cost,
                'total_cost' => -($quantity * (float) $balance->average_cost),
                'reason' => $data['reason'],
                'note' => $line['note'] ?? null,
            ]);
        }

        return $adjustment;
    }

    private function createAdjustment(Request $request, array $data, int $companyId, int $branchId, Warehouse $warehouse, int $locationId): StockAdjustment
    {
        return StockAdjustment::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'warehouse_id' => $warehouse->id,
            'stock_location_id' => $locationId,
            'adjustment_no' => DocumentNumber::make(StockAdjustment::class, 'adjustment_no', 'SA'),
            'adjustment_type' => $data['adjustment_type'],
            'status' => 'draft',
            'adjustment_date' => $data['adjustment_date'],
            'created_by' => $request->user()->id,
            'note' => $data['note'] ?? null,
        ]);
    }

    private function approveLineIn(Request $request, StockAdjustment $adjustment, StockAdjustmentLine $line, float $quantity): void
    {
        $balance = StockBalance::firstOrCreate(
            [
                'company_id' => $adjustment->company_id,
                'branch_id' => $adjustment->branch_id,
                'warehouse_id' => $adjustment->warehouse_id,
                'stock_location_id' => $line->stock_location_id,
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
        $targetQuantity = $before + $quantity;
        $targetValue = ($before * (float) $balance->average_cost) + ($quantity * (float) $line->unit_cost);

        $balance->update([
            'unit_id' => $line->unit_id,
            'quantity_on_hand' => (float) $balance->quantity_on_hand + $quantity,
            'quantity_available' => $targetQuantity,
            'average_cost' => $targetQuantity > 0 ? $targetValue / $targetQuantity : $line->unit_cost,
        ]);

        $this->recordMovement($request, $adjustment, $line, $quantity, $before, $before + $quantity);
    }

    private function approveLineOut(Request $request, StockAdjustment $adjustment, StockAdjustmentLine $line, float $quantity): void
    {
        $balance = StockBalance::query()
            ->where('company_id', $adjustment->company_id)
            ->where('branch_id', $adjustment->branch_id)
            ->where('warehouse_id', $adjustment->warehouse_id)
            ->where('stock_location_id', $line->stock_location_id)
            ->where('item_id', $line->item_id)
            ->lockForUpdate()
            ->first();

        if (! $balance || (float) $balance->quantity_available < $quantity) {
            throw ValidationException::withMessages(['lines' => 'Not enough available stock to approve this adjustment-out.']);
        }

        $before = (float) $balance->quantity_available;

        $balance->update([
            'quantity_on_hand' => (float) $balance->quantity_on_hand - $quantity,
            'quantity_available' => (float) $balance->quantity_available - $quantity,
        ]);

        $this->recordMovement($request, $adjustment, $line, -$quantity, $before, $before - $quantity);
    }

    private function recordMovement(Request $request, StockAdjustment $adjustment, StockAdjustmentLine $line, float $quantity, float $before, float $after): void
    {
        $movement = StockMovement::create([
            'company_id' => $adjustment->company_id,
            'branch_id' => $adjustment->branch_id,
            'warehouse_id' => $adjustment->warehouse_id,
            'from_location_id' => $adjustment->adjustment_type === 'adjustment_out' ? $line->stock_location_id : null,
            'to_location_id' => $adjustment->adjustment_type === 'adjustment_in' ? $line->stock_location_id : null,
            'item_id' => $line->item_id,
            'unit_id' => $line->unit_id,
            'movement_type' => $adjustment->adjustment_type,
            'quantity' => $quantity,
            'unit_cost' => $line->unit_cost,
            'total_cost' => $quantity * (float) $line->unit_cost,
            'reference_type' => 'stock_adjustment',
            'reference_id' => $adjustment->id,
            'reference_no' => $adjustment->adjustment_no,
            'movement_date' => $adjustment->adjustment_date ?? now(),
            'created_by' => $request->user()->id,
            'note' => $line->reason,
        ]);

        StockLog::create([
            'company_id' => $adjustment->company_id,
            'branch_id' => $adjustment->branch_id,
            'stock_movement_id' => $movement->id,
            'item_id' => $line->item_id,
            'action' => $adjustment->adjustment_type,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'quantity_changed' => $quantity,
            'reference_type' => 'stock_adjustment',
            'reference_id' => $adjustment->id,
            'reference_no' => $adjustment->adjustment_no,
            'performed_by' => $request->user()->id,
            'note' => $line->reason,
        ]);
    }

    private function scope(Request $request): array
    {
        $user = $request->user();
        $companyId = $user->company_id ?? Company::query()->value('id');
        $branchId = $user->branch_id ?? Branch::query()->where('company_id', $companyId)->value('id');

        abort_if(! $companyId, 422, 'No company is available for stock adjustment.');
        abort_if(! $branchId, 422, 'No branch is available for stock adjustment.');

        return [$companyId, $branchId];
    }

    private function ensureDraftAdjustment(Request $request, StockAdjustment $stockAdjustment): void
    {
        [$companyId, $branchId] = $this->scope($request);

        abort_if((int) $stockAdjustment->company_id !== (int) $companyId, 403);
        abort_if((int) $stockAdjustment->branch_id !== (int) $branchId, 403);
        abort_if(! in_array($stockAdjustment->adjustment_type, ['adjustment_in', 'adjustment_out'], true), 403);
        abort_if($stockAdjustment->status !== 'draft', 422, 'Only draft stock adjustments can be changed.');
    }

    private function statusFilter(string $status): string
    {
        return $status === 'approved' ? 'confirmed' : $status;
    }

    private function formatBalance(StockBalance $balance): array
    {
        return [
            'id' => $balance->id,
            'itemId' => $balance->item_id,
            'unitId' => $balance->unit_id,
            'warehouseId' => $balance->warehouse_id,
            'stockLocationId' => $balance->stock_location_id,
            'itemCode' => $balance->item?->code ?: 'ITEM-'.str_pad((string) $balance->item_id, 4, '0', STR_PAD_LEFT),
            'itemName' => $balance->item?->name ?? 'Unknown Item',
            'itemType' => $balance->item?->item_type ?? 'other',
            'branch' => $balance->branch?->name ?? 'Unassigned',
            'warehouse' => $balance->warehouse?->name ?? 'Warehouse',
            'location' => $balance->stockLocation?->name ?? 'Location',
            'locationCode' => $balance->stockLocation?->code ?? 'LOC',
            'locationType' => $balance->stockLocation?->location_type ?? 'general',
            'unit' => $balance->unit?->code ?? $balance->unit?->name ?? 'Unit',
            'quantityOnHand' => number_format((float) $balance->quantity_on_hand, 2),
            'quantityAvailable' => number_format((float) $balance->quantity_available, 2),
            'averageCost' => number_format((float) $balance->average_cost, 2),
            'stockValue' => number_format((float) $balance->quantity_available * (float) $balance->average_cost, 2),
        ];
    }

    private function formatAdjustment(StockAdjustment $adjustment): array
    {
        return [
            'id' => $adjustment->id,
            'code' => $adjustment->adjustment_no,
            'type' => $adjustment->adjustment_type,
            'date' => $adjustment->adjustment_date?->toDateString(),
            'displayDate' => $adjustment->adjustment_date?->format('M d, Y') ?? $adjustment->created_at?->format('M d, Y'),
            'warehouse' => $adjustment->warehouse?->name ?? 'Warehouse',
            'location' => $adjustment->stockLocation?->name ?? 'Location',
            'locationCode' => $adjustment->stockLocation?->code ?? 'LOC',
            'branch' => $adjustment->branch?->name ?? 'Unassigned',
            'itemCount' => (int) $adjustment->lines_count,
            'totalQuantity' => (float) $adjustment->lines->sum(fn (StockAdjustmentLine $line) => abs((float) $line->difference_quantity)),
            'totalCost' => (float) $adjustment->lines->sum(fn (StockAdjustmentLine $line) => abs((float) $line->total_cost)),
            'reason' => $adjustment->lines->pluck('reason')->filter()->first() ?? $adjustment->note,
            'createdBy' => $adjustment->creator?->name ?? 'System',
            'status' => $adjustment->status === 'confirmed' ? 'approved' : $adjustment->status,
            'lines' => $adjustment->lines->map(fn (StockAdjustmentLine $line): array => [
                'id' => $line->id,
                'itemCode' => $line->item?->code ?: 'ITEM-'.str_pad((string) $line->item_id, 4, '0', STR_PAD_LEFT),
                'itemName' => $line->item?->name ?? 'Unknown Item',
                'unit' => $line->unit?->code ?? $line->unit?->name ?? 'Unit',
                'location' => $line->stockLocation?->name ?? $adjustment->stockLocation?->name ?? 'Location',
                'locationCode' => $line->stockLocation?->code ?? $adjustment->stockLocation?->code ?? 'LOC',
                'systemQuantity' => (float) $line->system_quantity,
                'adjustedQuantity' => (float) $line->adjusted_quantity,
                'differenceQuantity' => (float) $line->difference_quantity,
                'unitCost' => (float) $line->unit_cost,
                'totalCost' => abs((float) $line->total_cost),
                'reason' => $line->reason,
                'note' => $line->note,
            ]),
        ];
    }
}
