<?php

namespace App\Http\Controllers\StockMovements;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentLine;
use App\Models\StockBalance;
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

class StockWriteOffController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('StockWriteOff/Index', $this->pageProps($request));
    }

    public function create(Request $request): Response
    {
        return Inertia::render('StockWriteOff/Create', $this->pageProps($request));
    }

    private function pageProps(Request $request): array
    {
        [$companyId, $branchId] = $this->scope($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'location_id' => ['nullable', 'integer'],
        ]);

        $inventory = StockBalance::query()
            ->with([
                'branch:id,name,code',
                'warehouse:id,name,code',
                'stockLocation:id,name,code,location_type',
                'item:id,name,code,item_type',
                'unit:id,name,code',
            ])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->where('quantity_available', '>', 0)
            ->whereHas('stockLocation', fn ($query) => $query->where('is_active', true))
            ->whereHas('item', fn ($query) => $query->where('is_active', true)->where('is_stockable', true))
            ->orderBy('warehouse_id')
            ->orderBy('stock_location_id')
            ->orderBy('item_id')
            ->get()
            ->map(fn (StockBalance $balance): array => [
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
            ]);

        $locations = $inventory
            ->unique('stockLocationId')
            ->map(fn (array $balance): array => [
                'id' => $balance['stockLocationId'],
                'warehouseId' => $balance['warehouseId'],
                'code' => $balance['locationCode'],
                'name' => $balance['location'],
                'warehouse' => $balance['warehouse'],
                'branch' => $balance['branch'],
            ])
            ->values();

        $warehouses = $inventory
            ->unique('warehouseId')
            ->map(fn (array $balance): array => [
                'id' => $balance['warehouseId'],
                'name' => $balance['warehouse'],
                'branch' => $balance['branch'],
            ])
            ->values();

        $items = Item::query()
            ->with('unit:id,name,code')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('is_stockable', true)
            ->when($branchId, fn ($query) => $query->where(function ($inner) use ($branchId) {
                $inner->whereNull('branch_id')->orWhere('branch_id', $branchId);
            }))
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

        $writeOffs = StockAdjustment::query()
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
            ->where('adjustment_type', 'write_off')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($filters['location_id'] ?? null, fn ($query, $locationId) => $query->where(function ($inner) use ($locationId) {
                $inner->where('stock_location_id', $locationId)
                    ->orWhereHas('lines', fn ($lineQuery) => $lineQuery->where('stock_location_id', $locationId));
            }))
            ->when($filters['status'] ?? null, function ($query, string $status) {
                if ($status === 'approved') {
                    $query->where('status', 'confirmed');

                    return;
                }

                if ($status === 'rejected') {
                    $query->where('status', 'cancelled')->where('cancel_reason', 'like', 'Rejected%');

                    return;
                }

                if ($status === 'cancelled') {
                    $query->where('status', 'cancelled')->where(function ($inner) {
                        $inner->whereNull('cancel_reason')
                            ->orWhere('cancel_reason', 'not like', 'Rejected%');
                    });

                    return;
                }

                $query->where('status', $status);
            })
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
            ->map(fn (StockAdjustment $adjustment): array => $this->formatWriteOff($adjustment));

        return [
            'inventory' => $inventory,
            'locations' => $locations,
            'warehouses' => $warehouses,
            'items' => $items,
            'writeOffs' => $writeOffs,
            'nextWriteOffNo' => DocumentNumber::make(StockAdjustment::class, 'adjustment_no', 'WO'),
            'filters' => [
                'search' => $filters['search'] ?? null,
                'status' => $filters['status'] ?? null,
                'location_id' => $filters['location_id'] ?? null,
            ],
        ];
    }

    public function store(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $request->validate([
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'adjustment_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['required', Rule::exists('items', 'id')->where('company_id', $companyId)],
            'lines.*.stock_balance_id' => ['required', Rule::exists('stock_balances', 'id')->where('company_id', $companyId)],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.note' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $data, $companyId, $branchId) {
            $warehouse = Warehouse::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->findOrFail($data['warehouse_id']);

            $balances = StockBalance::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->where('warehouse_id', $warehouse->id)
                ->whereIn('id', collect($data['lines'])->pluck('stock_balance_id')->unique())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach (collect($data['lines'])->groupBy('stock_balance_id') as $balanceId => $lines) {
                $balance = $balances->get((int) $balanceId);
                $quantity = $lines->sum(fn (array $line) => (float) $line['quantity']);

                if (! $balance) {
                    throw ValidationException::withMessages([
                        'lines' => 'Selected inventory is not available in this warehouse.',
                    ]);
                }

                if ($lines->contains(fn (array $line) => (int) $line['item_id'] !== (int) $balance->item_id)) {
                    throw ValidationException::withMessages([
                        'lines' => 'Selected location does not match the item.',
                    ]);
                }

                if ((float) $balance->quantity_available < $quantity) {
                    throw ValidationException::withMessages([
                        'lines' => 'Write-off quantity is greater than available stock.',
                    ]);
                }
            }

            $firstBalance = $balances->first();

            if (! $firstBalance) {
                throw ValidationException::withMessages([
                    'lines' => 'Select at least one stocked item location.',
                ]);
            }

            $adjustment = StockAdjustment::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'warehouse_id' => $warehouse->id,
                'stock_location_id' => $firstBalance->stock_location_id,
                'adjustment_no' => DocumentNumber::make(StockAdjustment::class, 'adjustment_no', 'WO'),
                'adjustment_type' => 'write_off',
                'status' => 'draft',
                'adjustment_date' => $data['adjustment_date'],
                'created_by' => $request->user()->id,
                'note' => $data['note'] ?? null,
            ]);

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
        });

        return redirect()->route('stock-movements.write-off')->with('success', 'Stock write-off saved as draft.');
    }

    public function approve(Request $request, StockAdjustment $stockAdjustment)
    {
        $this->ensureDraftWriteOff($request, $stockAdjustment);

        DB::transaction(function () use ($request, $stockAdjustment) {
            $stockAdjustment->load('lines');

            foreach ($stockAdjustment->lines as $line) {
                $quantity = abs((float) $line->difference_quantity);

                if ($quantity <= 0) {
                    continue;
                }

                $balance = StockBalance::query()
                    ->where('company_id', $stockAdjustment->company_id)
                    ->where('branch_id', $stockAdjustment->branch_id)
                    ->where('warehouse_id', $stockAdjustment->warehouse_id)
                    ->where('stock_location_id', $line->stock_location_id ?? $stockAdjustment->stock_location_id)
                    ->where('item_id', $line->item_id)
                    ->lockForUpdate()
                    ->first();

                if (! $balance || (float) $balance->quantity_available < $quantity) {
                    throw ValidationException::withMessages([
                        'lines' => 'Not enough available stock to approve this write-off.',
                    ]);
                }

                $before = (float) $balance->quantity_available;

                $balance->update([
                    'quantity_on_hand' => (float) $balance->quantity_on_hand - $quantity,
                    'quantity_available' => (float) $balance->quantity_available - $quantity,
                ]);

                $movement = StockMovement::create([
                    'company_id' => $stockAdjustment->company_id,
                    'branch_id' => $stockAdjustment->branch_id,
                    'warehouse_id' => $stockAdjustment->warehouse_id,
                    'from_location_id' => $line->stock_location_id ?? $stockAdjustment->stock_location_id,
                    'to_location_id' => null,
                    'item_id' => $line->item_id,
                    'unit_id' => $line->unit_id,
                    'movement_type' => 'write_off',
                    'quantity' => -$quantity,
                    'unit_cost' => $line->unit_cost,
                    'total_cost' => -($quantity * (float) $line->unit_cost),
                    'reference_type' => 'stock_adjustment',
                    'reference_id' => $stockAdjustment->id,
                    'reference_no' => $stockAdjustment->adjustment_no,
                    'movement_date' => now(),
                    'created_by' => $request->user()->id,
                    'note' => $line->reason,
                ]);

                StockLog::create([
                    'company_id' => $stockAdjustment->company_id,
                    'branch_id' => $stockAdjustment->branch_id,
                    'stock_movement_id' => $movement->id,
                    'item_id' => $line->item_id,
                    'action' => 'write_off',
                    'quantity_before' => $before,
                    'quantity_after' => $before - $quantity,
                    'quantity_changed' => -$quantity,
                    'reference_type' => 'stock_adjustment',
                    'reference_id' => $stockAdjustment->id,
                    'reference_no' => $stockAdjustment->adjustment_no,
                    'performed_by' => $request->user()->id,
                    'note' => $line->reason,
                ]);
            }

            $stockAdjustment->update([
                'status' => 'confirmed',
                'confirmed_by' => $request->user()->id,
                'confirmed_at' => now(),
            ]);
        });

        return back()->with('success', "Stock write-off {$stockAdjustment->adjustment_no} approved.");
    }

    public function reject(Request $request, StockAdjustment $stockAdjustment)
    {
        $this->ensureDraftWriteOff($request, $stockAdjustment);

        $stockAdjustment->update([
            'status' => 'cancelled',
            'cancelled_by' => $request->user()->id,
            'cancelled_at' => now(),
            'cancel_reason' => 'Rejected by approver',
        ]);

        return back()->with('success', "Stock write-off {$stockAdjustment->adjustment_no} rejected.");
    }

    public function cancel(Request $request, StockAdjustment $stockAdjustment)
    {
        $this->ensureDraftWriteOff($request, $stockAdjustment);

        $stockAdjustment->update([
            'status' => 'cancelled',
            'cancelled_by' => $request->user()->id,
            'cancelled_at' => now(),
            'cancel_reason' => 'Cancelled by user',
        ]);

        return back()->with('success', "Stock write-off {$stockAdjustment->adjustment_no} cancelled.");
    }

    private function scope(Request $request): array
    {
        $user = $request->user();
        $companyId = $user->company_id ?? Company::query()->value('id');
        $branchId = $user->branch_id ?? Branch::query()->where('company_id', $companyId)->value('id');

        abort_if(! $companyId, 422, 'No company is available for stock write-off.');
        abort_if(! $branchId, 422, 'No branch is available for stock write-off.');

        return [$companyId, $branchId];
    }

    private function ensureDraftWriteOff(Request $request, StockAdjustment $stockAdjustment): void
    {
        [$companyId, $branchId] = $this->scope($request);

        abort_if((int) $stockAdjustment->company_id !== (int) $companyId, 403);
        abort_if((int) $stockAdjustment->branch_id !== (int) $branchId, 403);
        abort_if($stockAdjustment->adjustment_type !== 'write_off', 403);
        abort_if($stockAdjustment->status !== 'draft', 422, 'Only draft stock write-offs can be changed.');
    }

    private function formatWriteOff(StockAdjustment $adjustment): array
    {
        return [
            'id' => $adjustment->id,
            'code' => $adjustment->adjustment_no,
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
            'status' => $this->formatStatus($adjustment),
            'lines' => $adjustment->lines->map(fn (StockAdjustmentLine $line): array => [
                'id' => $line->id,
                'itemCode' => $line->item?->code ?: 'ITEM-'.str_pad((string) $line->item_id, 4, '0', STR_PAD_LEFT),
                'itemName' => $line->item?->name ?? 'Unknown Item',
                'unit' => $line->unit?->code ?? $line->unit?->name ?? 'Unit',
                'location' => $line->stockLocation?->name ?? $adjustment->stockLocation?->name ?? 'Location',
                'locationCode' => $line->stockLocation?->code ?? $adjustment->stockLocation?->code ?? 'LOC',
                'systemQuantity' => (float) $line->system_quantity,
                'writeOffQuantity' => abs((float) $line->difference_quantity),
                'unitCost' => (float) $line->unit_cost,
                'totalCost' => abs((float) $line->total_cost),
                'reason' => $line->reason,
                'note' => $line->note,
            ]),
        ];
    }

    private function formatStatus(StockAdjustment $adjustment): string
    {
        if ($adjustment->status === 'confirmed') {
            return 'approved';
        }

        if ($adjustment->status === 'cancelled' && str_starts_with((string) $adjustment->cancel_reason, 'Rejected')) {
            return 'rejected';
        }

        return $adjustment->status;
    }
}
