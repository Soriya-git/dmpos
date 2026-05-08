<?php

namespace App\Http\Controllers\BalanceOnHand;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\StockBalance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BalanceOnHandController extends Controller
{
    public function index(Request $request): Response
    {
        [$companyId, $branchId] = $this->scope($request);

        $items = Item::query()
            ->with(['unit:id,name,code', 'stockBalances' => fn ($query) => $query
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))])
            ->where('company_id', $companyId)
            ->where('is_stockable', true)
            ->where('is_active', true)
            ->when($branchId, fn ($query) => $query->where(function ($inner) use ($branchId) {
                $inner->whereNull('branch_id')->orWhere('branch_id', $branchId);
            }))
            ->orderBy('name')
            ->get()
            ->map(function (Item $item): array {
                $quantityOnHand = $item->stockBalances->sum(fn (StockBalance $balance) => (float) $balance->quantity_on_hand);
                $quantityAvailable = $item->stockBalances->sum(fn (StockBalance $balance) => (float) $balance->quantity_available);
                $quantityReserved = $item->stockBalances->sum(fn (StockBalance $balance) => (float) $balance->quantity_reserved);
                $stockValue = $item->stockBalances->sum(fn (StockBalance $balance) => (float) $balance->quantity_available * (float) $balance->average_cost);
                $minimumStock = (float) $item->minimum_stock_qty;

                return [
                    'id' => $item->id,
                    'code' => $item->code ?: 'ITEM-'.str_pad((string) $item->id, 4, '0', STR_PAD_LEFT),
                    'name' => $item->name,
                    'itemType' => $item->item_type,
                    'unit' => $item->unit?->code ?? $item->unit?->name ?? 'Unit',
                    'minimumStockQty' => $minimumStock,
                    'quantityOnHand' => $quantityOnHand,
                    'quantityAvailable' => $quantityAvailable,
                    'quantityReserved' => $quantityReserved,
                    'stockValue' => $stockValue,
                    'locationsCount' => $item->stockBalances->where('quantity_available', '>', 0)->count(),
                    'status' => $this->stockStatus($quantityAvailable, $minimumStock),
                ];
            });

        return Inertia::render('BalanceOnHand/Index', [
            'items' => $items,
            'stats' => [
                'allItems' => $items->count(),
                'goodStock' => $items->where('status', 'good')->count(),
                'lowStock' => $items->where('status', 'low')->count(),
                'noStock' => $items->where('status', 'none')->count(),
            ],
        ]);
    }

    public function show(Request $request, Item $item): Response
    {
        [$companyId, $branchId] = $this->scope($request);

        abort_if((int) $item->company_id !== (int) $companyId, 403);
        abort_if($branchId && $item->branch_id && (int) $item->branch_id !== (int) $branchId, 403);

        $item->load('unit:id,name,code');

        $balances = StockBalance::query()
            ->with([
                'branch:id,name,code',
                'warehouse:id,name,code',
                'stockLocation:id,name,code,location_type,is_saleable',
                'unit:id,name,code',
            ])
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->where('item_id', $item->id)
            ->orderBy('warehouse_id')
            ->orderBy('stock_location_id')
            ->get();

        $quantityOnHand = $balances->sum(fn (StockBalance $balance) => (float) $balance->quantity_on_hand);
        $quantityAvailable = $balances->sum(fn (StockBalance $balance) => (float) $balance->quantity_available);
        $minimumStock = (float) $item->minimum_stock_qty;

        return Inertia::render('BalanceOnHand/View', [
            'item' => [
                'id' => $item->id,
                'code' => $item->code ?: 'ITEM-'.str_pad((string) $item->id, 4, '0', STR_PAD_LEFT),
                'name' => $item->name,
                'itemType' => $item->item_type,
                'unit' => $item->unit?->code ?? $item->unit?->name ?? 'Unit',
                'minimumStockQty' => $minimumStock,
                'quantityOnHand' => $quantityOnHand,
                'quantityAvailable' => $quantityAvailable,
                'quantityReserved' => $balances->sum(fn (StockBalance $balance) => (float) $balance->quantity_reserved),
                'stockValue' => $balances->sum(fn (StockBalance $balance) => (float) $balance->quantity_available * (float) $balance->average_cost),
                'status' => $this->stockStatus($quantityAvailable, $minimumStock),
            ],
            'balances' => $balances->map(fn (StockBalance $balance): array => [
                'id' => $balance->id,
                'branch' => $balance->branch?->name ?? 'Unassigned',
                'warehouse' => $balance->warehouse?->name ?? 'Warehouse',
                'warehouseCode' => $balance->warehouse?->code ?? 'WH',
                'location' => $balance->stockLocation?->name ?? 'Location',
                'locationCode' => $balance->stockLocation?->code ?? 'LOC',
                'locationType' => $balance->stockLocation?->location_type ?? 'general',
                'isSaleable' => (bool) $balance->stockLocation?->is_saleable,
                'unit' => $balance->unit?->code ?? $balance->unit?->name ?? 'Unit',
                'quantityOnHand' => (float) $balance->quantity_on_hand,
                'quantityReserved' => (float) $balance->quantity_reserved,
                'quantityAvailable' => (float) $balance->quantity_available,
                'averageCost' => (float) $balance->average_cost,
                'stockValue' => (float) $balance->quantity_available * (float) $balance->average_cost,
            ]),
        ]);
    }

    private function scope(Request $request): array
    {
        $user = $request->user();
        $companyId = $user->company_id ?? Company::query()->value('id');
        $branchId = $user->branch_id ?? Branch::query()->where('company_id', $companyId)->value('id');

        abort_if(! $companyId, 422, 'No company is available for balance on hand.');

        return [$companyId, $branchId];
    }

    private function stockStatus(float $quantityAvailable, float $minimumStock): string
    {
        if ($quantityAvailable <= 0) {
            return 'none';
        }

        if ($quantityAvailable <= $minimumStock) {
            return 'low';
        }

        return 'good';
    }
}
