<?php

namespace App\Http\Controllers\MasterData;

use App\Models\StockLocation;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseLocationController
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->company_id;

        $warehouses = Warehouse::query()
            ->with('branch:id,name,code')
            ->withCount('stockLocations')
            ->withSum('stockBalances as quantity_on_hand', 'quantity_on_hand')
            ->withSum('stockBalances as quantity_available', 'quantity_available')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('name')
            ->get()
            ->map(fn (Warehouse $warehouse): array => [
                'id' => $warehouse->id,
                'code' => $warehouse->code ?: 'WH-'.str_pad((string) $warehouse->id, 3, '0', STR_PAD_LEFT),
                'name' => $warehouse->name,
                'branch' => $warehouse->branch?->name ?? 'Unassigned',
                'address' => $warehouse->address,
                'locationsCount' => (int) $warehouse->stock_locations_count,
                'quantityOnHand' => number_format((float) ($warehouse->quantity_on_hand ?? 0), 2),
                'quantityAvailable' => number_format((float) ($warehouse->quantity_available ?? 0), 2),
                'isDefault' => (bool) $warehouse->is_default,
                'description' => $warehouse->description,
                'status' => $warehouse->is_active ? 'approved' : 'cancelled',
            ]);

        $locations = StockLocation::query()
            ->with(['branch:id,name,code', 'warehouse:id,name,code'])
            ->withSum('stockBalances as quantity_on_hand', 'quantity_on_hand')
            ->withSum('stockBalances as quantity_available', 'quantity_available')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('warehouse_id')
            ->orderBy('location_type')
            ->orderBy('name')
            ->get()
            ->map(fn (StockLocation $location): array => [
                'id' => $location->id,
                'code' => $location->code ?: 'LOC-'.str_pad((string) $location->id, 3, '0', STR_PAD_LEFT),
                'name' => $location->name,
                'warehouse' => $location->warehouse?->name ?? 'Warehouse',
                'branch' => $location->branch?->name ?? 'Unassigned',
                'locationType' => $location->location_type,
                'isSaleable' => (bool) $location->is_saleable,
                'quantityOnHand' => number_format((float) ($location->quantity_on_hand ?? 0), 2),
                'quantityAvailable' => number_format((float) ($location->quantity_available ?? 0), 2),
                'description' => $location->description,
                'status' => $location->is_active ? 'approved' : 'cancelled',
            ]);

        return Inertia::render('MasterData/WarehouseLocation', [
            'warehouses' => $warehouses,
            'locations' => $locations,
        ]);
    }
}
