<?php

namespace Database\Seeders;

use App\Models\Branch;
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
        $branches = Branch::query()
            ->with('company')
            ->orderBy('company_id')
            ->orderBy('id')
            ->get();

        if ($branches->isEmpty()) {
            return;
        }

        foreach ($branches as $branch) {
            $company = $branch->company;

            if (! $company) {
                continue;
            }

            $warehouse = Warehouse::query()
                ->where('branch_id', $branch->id)
                ->where('is_default', true)
                ->first();

            if (! $warehouse) {
                $warehouse = Warehouse::query()->firstOrNew([
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'code' => $branch->code.'-WH',
                ]);
            }

            $warehouse->fill([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => $branch->name.' Warehouse',
                'code' => $warehouse->code ?: $branch->code.'-WH',
                'is_default' => true,
                'is_active' => true,
            ])->save();

            Warehouse::query()
                ->where('branch_id', $branch->id)
                ->whereKeyNot($warehouse->id)
                ->update(['is_default' => false]);

            $locations = [
                [
                    'name' => 'Staging Zone',
                    'code' => 'STAGE',
                    'location_type' => 'inbound_staging',
                    'is_saleable' => false,
                    'description' => 'Temporary receiving area before putaway.',
                ],
                [
                    'name' => 'Storage Zone A',
                    'code' => 'STOR-A',
                    'location_type' => 'putaway',
                    'is_saleable' => true,
                    'description' => 'Primary saleable storage location for putaway stock.',
                ],
                [
                    'name' => 'Storage Zone B',
                    'code' => 'STOR-B',
                    'location_type' => 'putaway',
                    'is_saleable' => true,
                    'description' => 'Secondary saleable storage location for putaway stock.',
                ],
                [
                    'name' => 'Storage Zone C',
                    'code' => 'STOR-C',
                    'location_type' => 'putaway',
                    'is_saleable' => true,
                    'description' => 'Bulk saleable storage location for putaway stock.',
                ],
                [
                    'name' => 'Storage Zone D',
                    'code' => 'STOR-D',
                    'location_type' => 'putaway',
                    'is_saleable' => true,
                    'description' => 'Reserve saleable storage location for putaway stock.',
                ],
                [
                    'name' => 'Storage Zone E',
                    'code' => 'STOR-E',
                    'location_type' => 'putaway',
                    'is_saleable' => true,
                    'description' => 'Overflow saleable storage location for putaway stock.',
                ],
                [
                    'name' => 'Damage / Expired Zone',
                    'code' => 'DAMAGE',
                    'location_type' => 'damage',
                    'is_saleable' => false,
                    'description' => 'Hold damaged, expired, or quality issue stock.',
                ],
                [
                    'name' => 'Obsolete Zone',
                    'code' => 'OBSOLETE',
                    'location_type' => 'obsolete',
                    'is_saleable' => false,
                    'description' => 'Hold obsolete stock awaiting review or write-off.',
                ],
                [
                    'name' => 'Goods Issue / Scrap Zone',
                    'code' => 'SCRAP',
                    'location_type' => 'scrap',
                    'is_saleable' => false,
                    'description' => 'Hold scrap and other non-saleable goods issue stock.',
                ],
            ];

            foreach ($locations as $location) {
                StockLocation::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'warehouse_id' => $warehouse->id,
                        'code' => $location['code'],
                    ],
                    [
                        'company_id' => $company->id,
                        'name' => $location['name'],
                        'location_type' => $location['location_type'],
                        'is_saleable' => $location['is_saleable'],
                        'description' => $location['description'],
                        'is_active' => true,
                    ]
                );
            }

            $putawayLocation = StockLocation::where('warehouse_id', $warehouse->id)
                ->where('location_type', 'putaway')
                ->first();

            if (! $putawayLocation) {
                continue;
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

                $item->update([
                    'minimum_stock_qty' => match ($item->code) {
                        'RM-RICE' => 25,
                        'RM-EGG' => 80,
                        'DRK-COKE' => 40,
                        default => 12,
                    },
                ]);

                StockBalance::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'warehouse_id' => $warehouse->id,
                        'stock_location_id' => $putawayLocation->id,
                        'item_id' => $item->id,
                    ],
                    [
                        'company_id' => $company->id,
                        'unit_id' => $item->unit_id,
                        'quantity_on_hand' => $openingQty,
                        'quantity_reserved' => 0,
                        'quantity_available' => $openingQty,
                        'average_cost' => $item->cost,
                    ]
                );

                StockMovement::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'item_id' => $item->id,
                        'reference_type' => 'opening_balance',
                        'reference_no' => 'OPENING-STOCK-'.$branch->code,
                    ],
                    [
                        'company_id' => $company->id,
                        'warehouse_id' => $warehouse->id,
                        'from_location_id' => null,
                        'to_location_id' => $putawayLocation->id,
                        'unit_id' => $item->unit_id,
                        'movement_type' => 'adjustment_in',
                        'quantity' => $openingQty,
                        'unit_cost' => $item->cost,
                        'total_cost' => $openingQty * $item->cost,
                        'movement_date' => now(),
                        'note' => 'Demo opening stock balance',
                    ]
                );
            }
        }
    }
}
