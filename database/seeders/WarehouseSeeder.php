<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $branch = Branch::first();

        if (! $company || ! $branch) {
            return;
        }

        $warehouse = Warehouse::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Main Warehouse',
            'code' => 'WH-MAIN',
            'is_default' => true,
            'is_active' => true,
        ]);

        $locations = [
            [
                'name' => 'Inbound Staging Area',
                'code' => 'IN-STAGE',
                'location_type' => 'inbound_staging',
                'is_saleable' => false,
            ],
            [
                'name' => 'Putaway Stock Area',
                'code' => 'PUTAWAY',
                'location_type' => 'putaway',
                'is_saleable' => true,
            ],
            [
                'name' => 'Outbound Staging Area',
                'code' => 'OUT-STAGE',
                'location_type' => 'outbound_staging',
                'is_saleable' => false,
            ],
            [
                'name' => 'Scrap Zone',
                'code' => 'SCRAP',
                'location_type' => 'scrap',
                'is_saleable' => false,
            ],
            [
                'name' => 'Damage Zone',
                'code' => 'DAMAGE',
                'location_type' => 'damage',
                'is_saleable' => false,
            ],
            [
                'name' => 'Obsolete Zone',
                'code' => 'OBSOLETE',
                'location_type' => 'obsolete',
                'is_saleable' => false,
            ],
        ];

        foreach ($locations as $location) {
            StockLocation::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouse->id,
                'name' => $location['name'],
                'code' => $location['code'],
                'location_type' => $location['location_type'],
                'is_saleable' => $location['is_saleable'],
                'is_active' => true,
            ]);
        }

        $putawayLocation = StockLocation::where('warehouse_id', $warehouse->id)
            ->where('location_type', 'putaway')
            ->first();

        if (! $putawayLocation) {
            return;
        }

        $items = Item::where('company_id', $company->id)
            ->where('is_stockable', true)
            ->get();

        foreach ($items as $item) {
            $openingQty = match ($item->code) {
                'RM-RICE' => 50,
                'RM-EGG' => 200,
                'DRK-COKE' => 100,
                default => 10,
            };

            StockBalance::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouse->id,
                'stock_location_id' => $putawayLocation->id,
                'item_id' => $item->id,
                'unit_id' => $item->unit_id,
                'quantity_on_hand' => $openingQty,
                'quantity_reserved' => 0,
                'quantity_available' => $openingQty,
                'average_cost' => $item->cost,
            ]);

            StockMovement::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouse->id,
                'from_location_id' => null,
                'to_location_id' => $putawayLocation->id,
                'item_id' => $item->id,
                'unit_id' => $item->unit_id,
                'movement_type' => 'adjustment_in',
                'quantity' => $openingQty,
                'unit_cost' => $item->cost,
                'total_cost' => $openingQty * $item->cost,
                'reference_type' => 'opening_balance',
                'reference_no' => 'OPENING-STOCK',
                'movement_date' => now(),
                'note' => 'Demo opening stock balance',
            ]);
        }
    }
}
