<?php

namespace App\Http\Controllers\StockMovements;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockLog;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferLine;
use App\Models\Warehouse;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InternalTransferController extends Controller
{
    private const LOCATION_TYPES = ['putaway', 'damage', 'obsolete', 'scrap'];

    public function index(Request $request): Response
    {
        return Inertia::render('InternalTransfer/Index', $this->pageProps($request));
    }

    public function create(Request $request): Response
    {
        return Inertia::render('InternalTransfer/Create', $this->pageProps($request));
    }

    private function pageProps(Request $request): array
    {
        [$companyId, $branchId] = $this->scope($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'location_id' => ['nullable', 'integer'],
            'warehouse_id' => ['nullable', 'integer'],
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
            ->whereHas('stockLocation', fn ($query) => $query
                ->where('is_active', true)
                ->whereIn('location_type', self::LOCATION_TYPES))
            ->whereHas('item', fn ($query) => $query->where('is_active', true)->where('is_stockable', true))
            ->orderBy('warehouse_id')
            ->orderBy('stock_location_id')
            ->orderBy('item_id')
            ->get()
            ->map(fn (StockBalance $balance): array => $this->formatBalance($balance));

        $locations = StockLocation::query()
            ->with('warehouse:id,name,code')
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereIn('location_type', self::LOCATION_TYPES)
            ->orderBy('warehouse_id')
            ->orderBy('location_type')
            ->orderBy('code')
            ->get()
            ->map(fn (StockLocation $location): array => [
                'id' => $location->id,
                'warehouseId' => $location->warehouse_id,
                'code' => $location->code ?? 'LOC',
                'name' => $location->name,
                'warehouse' => $location->warehouse?->name ?? 'Warehouse',
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
                'hasStock' => $inventory->contains('itemId', $item->id),
            ]);

        $transfers = StockTransfer::query()
            ->with([
                'creator:id,name',
                'approver:id,name',
                'canceller:id,name',
                'fromWarehouse:id,name,code',
                'toWarehouse:id,name,code',
                'fromLocation:id,name,code,location_type',
                'toLocation:id,name,code,location_type',
                'lines.item:id,name,code',
                'lines.unit:id,name,code',
                'lines.toLocation:id,name,code,location_type,warehouse_id',
                'lines.toLocation.warehouse:id,name,code',
            ])
            ->withCount('lines')
            ->where('company_id', $companyId)
            ->where('from_branch_id', $branchId)
            ->where('transfer_type', 'internal_transfer')
            ->whereNull('goods_receipt_id')
            ->when($filters['warehouse_id'] ?? null, fn ($query, $warehouseId) => $query
                ->where(fn ($inner) => $inner
                    ->where('from_warehouse_id', $warehouseId)
                    ->orWhere('to_warehouse_id', $warehouseId)))
            ->when($filters['location_id'] ?? null, fn ($query, $locationId) => $query
                ->where(fn ($inner) => $inner
                    ->where('from_location_id', $locationId)
                    ->orWhere('to_location_id', $locationId)
                    ->orWhereHas('lines.toLocation', fn ($lineQuery) => $lineQuery->whereKey($locationId))))
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('transfer_no', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhereHas('lines.item', fn ($lineQuery) => $lineQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->latest('transfer_date')
            ->latest()
            ->get()
            ->map(fn (StockTransfer $transfer): array => $this->formatTransfer($transfer));

        return [
            'transfers' => $transfers,
            'inventory' => $inventory,
            'locations' => $locations,
            'warehouses' => $warehouses,
            'items' => $items,
            'nextTransferNo' => DocumentNumber::make(StockTransfer::class, 'transfer_no', 'IT'),
            'filters' => [
                'search' => $filters['search'] ?? null,
                'location_id' => $filters['location_id'] ?? null,
                'warehouse_id' => $filters['warehouse_id'] ?? null,
            ],
        ];
    }

    public function store(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $request->validate([
            'transfer_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['required', Rule::exists('items', 'id')->where('company_id', $companyId)],
            'lines.*.from_warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'lines.*.stock_balance_id' => ['required', Rule::exists('stock_balances', 'id')->where('company_id', $companyId)],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.to_warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('company_id', $companyId)],
            'lines.*.to_location_id' => ['required', Rule::exists('stock_locations', 'id')->where('company_id', $companyId)],
            'lines.*.note' => ['nullable', 'string'],
        ]);

        $transfer = DB::transaction(function () use ($request, $data, $companyId, $branchId): StockTransfer {
            $balanceIds = collect($data['lines'])->pluck('stock_balance_id')->unique()->values();
            $balances = StockBalance::query()
                ->with('stockLocation')
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->whereIn('id', $balanceIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $destinationIds = collect($data['lines'])->pluck('to_location_id')->unique()->values();
            $destinations = StockLocation::query()
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('is_active', true)
                ->whereIn('location_type', self::LOCATION_TYPES)
                ->whereIn('id', $destinationIds)
                ->get()
                ->keyBy('id');

            foreach (collect($data['lines'])->groupBy('stock_balance_id') as $balanceId => $lines) {
                $balance = $balances->get((int) $balanceId);
                $quantity = $lines->sum(fn (array $line) => (float) $line['quantity']);

                if (! $balance || ! $balance->stockLocation || ! in_array($balance->stockLocation->location_type, self::LOCATION_TYPES, true)) {
                    throw ValidationException::withMessages(['lines' => 'Selected source stock is not available for internal transfer.']);
                }

                if ((float) $balance->quantity_available < $quantity) {
                    throw ValidationException::withMessages(['lines' => 'Transfer quantity is greater than available stock.']);
                }
            }

            foreach ($data['lines'] as $line) {
                $balance = $balances->get((int) $line['stock_balance_id']);
                $destination = $destinations->get((int) $line['to_location_id']);

                if (! $balance || (int) $balance->item_id !== (int) $line['item_id']) {
                    throw ValidationException::withMessages(['lines' => 'Selected item does not match the source stock.']);
                }

                if ((int) $balance->warehouse_id !== (int) $line['from_warehouse_id']) {
                    throw ValidationException::withMessages(['lines' => 'Selected source warehouse does not match the source location.']);
                }

                if (! $destination || (int) $destination->warehouse_id !== (int) $line['to_warehouse_id']) {
                    throw ValidationException::withMessages(['lines' => 'Selected destination location does not match the warehouse.']);
                }

                if ((int) $balance->stock_location_id === (int) $destination->id) {
                    throw ValidationException::withMessages(['lines' => 'Source and destination location must be different.']);
                }
            }

            $firstLine = $data['lines'][0];
            $firstBalance = $balances->get((int) $firstLine['stock_balance_id']);
            $firstDestination = $destinations->get((int) $firstLine['to_location_id']);
            $transferNo = DocumentNumber::make(StockTransfer::class, 'transfer_no', 'IT');

            $transfer = StockTransfer::create([
                'company_id' => $companyId,
                'from_branch_id' => $branchId,
                'to_branch_id' => $branchId,
                'from_warehouse_id' => $firstBalance->warehouse_id,
                'to_warehouse_id' => $firstDestination->warehouse_id,
                'from_location_id' => $firstBalance->stock_location_id,
                'to_location_id' => $firstDestination->id,
                'transfer_no' => $transferNo,
                'transfer_type' => 'internal_transfer',
                'status' => 'draft',
                'transfer_date' => $data['transfer_date'],
                'created_by' => $request->user()->id,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($data['lines'] as $line) {
                $source = $balances->get((int) $line['stock_balance_id']);
                $destination = $destinations->get((int) $line['to_location_id']);
                $quantity = (float) $line['quantity'];
                $unitCost = (float) $source->average_cost;

                StockTransferLine::create([
                    'stock_transfer_id' => $transfer->id,
                    'item_id' => $source->item_id,
                    'unit_id' => $source->unit_id,
                    'to_location_id' => $destination->id,
                    'quantity_requested' => $quantity,
                    'quantity_dispatched' => 0,
                    'quantity_received' => 0,
                    'unit_cost' => $unitCost,
                    'total_cost' => $quantity * $unitCost,
                    'status' => 'open',
                    'note' => trim("From balance: {$source->id}\n".($line['note'] ?? '')),
                ]);
            }

            return $transfer;
        });

        return redirect()
            ->route('stock-movements.internal-transfer')
            ->with('success', "Internal transfer {$transfer->transfer_no} saved as draft.");
    }

    public function approve(Request $request, StockTransfer $stockTransfer)
    {
        $this->ensureDraftTransfer($request, $stockTransfer);

        DB::transaction(function () use ($request, $stockTransfer) {
            $stockTransfer->load(['lines.toLocation']);

            foreach ($stockTransfer->lines as $line) {
                $sourceBalanceId = $this->sourceBalanceId($line);
                $quantity = (float) $line->quantity_requested;

                if ($quantity <= 0 || ! $sourceBalanceId) {
                    throw ValidationException::withMessages([
                        'lines' => 'Internal transfer line is missing source stock.',
                    ]);
                }

                $source = StockBalance::query()
                    ->with('stockLocation')
                    ->where('company_id', $stockTransfer->company_id)
                    ->where('branch_id', $stockTransfer->from_branch_id)
                    ->whereKey($sourceBalanceId)
                    ->lockForUpdate()
                    ->first();

                if (! $source || (int) $source->item_id !== (int) $line->item_id) {
                    throw ValidationException::withMessages([
                        'lines' => 'Selected source stock no longer matches this transfer.',
                    ]);
                }

                if ((float) $source->quantity_available < $quantity) {
                    throw ValidationException::withMessages([
                        'lines' => 'Transfer quantity is greater than available stock.',
                    ]);
                }

                $destination = StockLocation::query()
                    ->where('company_id', $stockTransfer->company_id)
                    ->where('branch_id', $stockTransfer->to_branch_id)
                    ->where('is_active', true)
                    ->whereIn('location_type', self::LOCATION_TYPES)
                    ->find($line->to_location_id);

                if (! $destination) {
                    throw ValidationException::withMessages([
                        'lines' => 'Destination location is no longer available.',
                    ]);
                }

                $unitCost = (float) $line->unit_cost;
                $sourceBefore = (float) $source->quantity_available;

                $source->update([
                    'quantity_on_hand' => (float) $source->quantity_on_hand - $quantity,
                    'quantity_available' => (float) $source->quantity_available - $quantity,
                ]);

                $target = StockBalance::firstOrCreate(
                    [
                        'company_id' => $stockTransfer->company_id,
                        'branch_id' => $stockTransfer->to_branch_id,
                        'warehouse_id' => $destination->warehouse_id,
                        'stock_location_id' => $destination->id,
                        'item_id' => $source->item_id,
                    ],
                    [
                        'unit_id' => $source->unit_id,
                        'quantity_on_hand' => 0,
                        'quantity_reserved' => 0,
                        'quantity_available' => 0,
                        'average_cost' => $source->average_cost,
                    ]
                );

                $targetBefore = (float) $target->quantity_available;
                $targetQuantity = $targetBefore + $quantity;
                $targetValue = ($targetBefore * (float) $target->average_cost) + ($quantity * $unitCost);

                $target->update([
                    'unit_id' => $source->unit_id,
                    'quantity_on_hand' => (float) $target->quantity_on_hand + $quantity,
                    'quantity_available' => $targetQuantity,
                    'average_cost' => $targetQuantity > 0 ? $targetValue / $targetQuantity : $unitCost,
                ]);

                $line->update([
                    'quantity_dispatched' => $quantity,
                    'quantity_received' => $quantity,
                    'status' => 'received',
                ]);

                $movement = StockMovement::create([
                    'company_id' => $stockTransfer->company_id,
                    'branch_id' => $stockTransfer->from_branch_id,
                    'warehouse_id' => $source->warehouse_id,
                    'from_location_id' => $source->stock_location_id,
                    'to_location_id' => $destination->id,
                    'item_id' => $source->item_id,
                    'unit_id' => $source->unit_id,
                    'movement_type' => 'internal_transfer',
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $quantity * $unitCost,
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $stockTransfer->id,
                    'reference_no' => $stockTransfer->transfer_no,
                    'movement_date' => $stockTransfer->transfer_date ?? now(),
                    'created_by' => $request->user()->id,
                    'note' => $stockTransfer->note,
                ]);

                StockLog::create([
                    'company_id' => $stockTransfer->company_id,
                    'branch_id' => $stockTransfer->from_branch_id,
                    'stock_movement_id' => $movement->id,
                    'item_id' => $source->item_id,
                    'action' => 'internal_transfer',
                    'quantity_before' => $sourceBefore,
                    'quantity_after' => $sourceBefore - $quantity,
                    'quantity_changed' => -$quantity,
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

        return back()->with('success', "Internal transfer {$stockTransfer->transfer_no} approved.");
    }

    public function reject(Request $request, StockTransfer $stockTransfer)
    {
        $this->ensureDraftTransfer($request, $stockTransfer);

        $stockTransfer->update([
            'status' => 'rejected',
            'cancelled_at' => now(),
            'cancelled_by' => $request->user()->id,
            'cancel_reason' => 'Rejected by approver',
        ]);

        return back()->with('success', "Internal transfer {$stockTransfer->transfer_no} rejected.");
    }

    public function cancel(Request $request, StockTransfer $stockTransfer)
    {
        $this->ensureDraftTransfer($request, $stockTransfer);

        $stockTransfer->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $request->user()->id,
            'cancel_reason' => 'Cancelled by user',
        ]);

        return back()->with('success', "Internal transfer {$stockTransfer->transfer_no} cancelled.");
    }

    private function scope(Request $request): array
    {
        $user = $request->user();
        $companyId = $user->company_id ?? Company::query()->value('id');
        $branchId = $user->branch_id ?? Branch::query()->where('company_id', $companyId)->value('id');

        abort_if(! $companyId, 422, 'No company is available for internal transfer.');
        abort_if(! $branchId, 422, 'No branch is available for internal transfer.');

        return [$companyId, $branchId];
    }

    private function ensureDraftTransfer(Request $request, StockTransfer $stockTransfer): void
    {
        [$companyId, $branchId] = $this->scope($request);

        abort_if((int) $stockTransfer->company_id !== (int) $companyId, 403);
        abort_if((int) $stockTransfer->from_branch_id !== (int) $branchId, 403);
        abort_if($stockTransfer->transfer_type !== 'internal_transfer' || $stockTransfer->goods_receipt_id, 403);
        abort_if($stockTransfer->status !== 'draft', 422, 'Only draft internal transfers can be changed.');
    }

    private function sourceBalanceId(StockTransferLine $line): ?int
    {
        if (! preg_match('/From balance:\s*(\d+)/', (string) $line->note, $matches)) {
            return null;
        }

        return (int) $matches[1];
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

    private function formatTransfer(StockTransfer $transfer): array
    {
        $movementGroups = StockMovement::query()
            ->with(['fromLocation:id,name,code,location_type,warehouse_id', 'fromLocation.warehouse:id,name,code', 'toLocation:id,name,code,location_type,warehouse_id', 'toLocation.warehouse:id,name,code'])
            ->where('reference_type', 'stock_transfer')
            ->where('reference_id', $transfer->id)
            ->where('movement_type', 'internal_transfer')
            ->get()
            ->groupBy('item_id');

        $sourceBalances = StockBalance::query()
            ->with(['stockLocation:id,name,code,location_type,warehouse_id', 'stockLocation.warehouse:id,name,code'])
            ->whereIn('id', $transfer->lines->map(fn (StockTransferLine $line) => $this->sourceBalanceId($line))->filter()->unique())
            ->get()
            ->keyBy('id');

        return [
            'id' => $transfer->id,
            'code' => $transfer->transfer_no,
            'date' => $transfer->transfer_date?->toDateString(),
            'displayDate' => $transfer->transfer_date?->format('M d, Y') ?? $transfer->created_at?->format('M d, Y'),
            'approvedAt' => $transfer->approved_at?->format('M d, Y H:i'),
            'cancelledAt' => $transfer->cancelled_at?->format('M d, Y H:i'),
            'fromWarehouse' => $transfer->fromWarehouse?->name ?? 'Warehouse',
            'toWarehouse' => $transfer->toWarehouse?->name ?? 'Warehouse',
            'fromLocation' => $transfer->fromLocation?->name ?? 'Location',
            'toLocation' => $transfer->toLocation?->name ?? 'Location',
            'itemCount' => (int) $transfer->lines_count,
            'totalQuantity' => (float) $transfer->lines->sum(fn (StockTransferLine $line) => (float) ($transfer->status === 'received' ? $line->quantity_received : $line->quantity_requested)),
            'totalCost' => (float) $transfer->lines->sum(fn (StockTransferLine $line) => (float) $line->total_cost),
            'note' => $transfer->note,
            'createdBy' => $transfer->creator?->name ?? 'System',
            'approvedBy' => $transfer->approver?->name,
            'cancelledBy' => $transfer->canceller?->name,
            'actionStatus' => $this->actionStatus($transfer),
            'status' => $transfer->status,
            'lines' => $transfer->lines->map(function (StockTransferLine $line) use (&$movementGroups, $sourceBalances, $transfer): array {
                $movement = $movementGroups->get($line->item_id)?->shift();
                $sourceBalance = $sourceBalances->get($this->sourceBalanceId($line));
                $quantity = (float) ($transfer->status === 'received' ? $line->quantity_received : $line->quantity_requested);

                return [
                    'id' => $line->id,
                    'itemCode' => $line->item?->code ?: 'ITEM-'.str_pad((string) $line->item_id, 4, '0', STR_PAD_LEFT),
                    'itemName' => $line->item?->name ?? 'Unknown Item',
                    'unit' => $line->unit?->code ?? $line->unit?->name ?? 'Unit',
                    'quantity' => $quantity,
                    'unitCost' => (float) $line->unit_cost,
                    'totalCost' => (float) $line->total_cost,
                    'fromWarehouse' => $movement?->fromLocation?->warehouse?->name ?? $sourceBalance?->stockLocation?->warehouse?->name ?? null,
                    'fromLocation' => $movement?->fromLocation?->name ?? $sourceBalance?->stockLocation?->name ?? null,
                    'fromLocationCode' => $movement?->fromLocation?->code ?? $sourceBalance?->stockLocation?->code ?? null,
                    'fromLocationType' => $movement?->fromLocation?->location_type ?? $sourceBalance?->stockLocation?->location_type ?? null,
                    'toWarehouse' => $movement?->toLocation?->warehouse?->name ?? $line->toLocation?->warehouse?->name ?? null,
                    'toLocation' => $movement?->toLocation?->name ?? $line->toLocation?->name ?? null,
                    'toLocationCode' => $movement?->toLocation?->code ?? $line->toLocation?->code ?? null,
                    'toLocationType' => $movement?->toLocation?->location_type ?? $line->toLocation?->location_type ?? null,
                    'note' => $line->note,
                ];
            }),
        ];
    }

    private function actionStatus(StockTransfer $transfer): ?string
    {
        if ($transfer->status === 'received' || $transfer->status === 'approved') {
            return 'Approved';
        }

        if ($transfer->status === 'rejected') {
            return 'Rejected';
        }

        if ($transfer->status === 'cancelled') {
            return 'Cancelled';
        }

        return null;
    }
}
