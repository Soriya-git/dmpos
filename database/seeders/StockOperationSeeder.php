<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentLine;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferLine;
use App\Models\Warehouse;
use App\Support\DocumentNumber;
use Illuminate\Database\Seeder;

class StockOperationSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $branch = Branch::first();
        $warehouse = Warehouse::first();
        $putawayLocation = StockLocation::where('location_type', 'putaway')->first();
        $damageLocation = StockLocation::where('location_type', 'damage')->first();

        if (! $company || ! $branch || ! $warehouse || ! $putawayLocation || ! $damageLocation) {
            return;
        }

        $item = Item::where('company_id', $company->id)
            ->where('is_stockable', true)
            ->where('is_active', true)
            ->first();

        if (! $item) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Demo Manual Adjustment Out
        |--------------------------------------------------------------------------
        */
        $adjustment = StockAdjustment::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'stock_location_id' => $putawayLocation->id,
            'adjustment_no' => DocumentNumber::make(StockAdjustment::class, 'adjustment_no', 'SA'),
            'adjustment_type' => 'adjustment_out',
            'status' => 'confirmed',
            'adjustment_date' => now(),
            'confirmed_at' => now(),
            'note' => 'Demo manual stock adjustment out',
        ]);

        $systemQty = StockBalance::where('branch_id', $branch->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('stock_location_id', $putawayLocation->id)
            ->where('item_id', $item->id)
            ->value('quantity_on_hand') ?? 0;

        $adjustQty = 2;

        StockAdjustmentLine::create([
            'stock_adjustment_id' => $adjustment->id,
            'item_id' => $item->id,
            'unit_id' => $item->unit_id,
            'system_quantity' => $systemQty,
            'adjusted_quantity' => $systemQty - $adjustQty,
            'difference_quantity' => -$adjustQty,
            'unit_cost' => $item->cost,
            'total_cost' => $adjustQty * $item->cost,
            'reason' => 'Demo adjustment out',
        ]);

        $balance = StockBalance::where('branch_id', $branch->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('stock_location_id', $putawayLocation->id)
            ->where('item_id', $item->id)
            ->first();

        if ($balance) {
            $balance->update([
                'quantity_on_hand' => $balance->quantity_on_hand - $adjustQty,
                'quantity_available' => $balance->quantity_available - $adjustQty,
            ]);
        }

        StockMovement::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'from_location_id' => $putawayLocation->id,
            'to_location_id' => null,
            'item_id' => $item->id,
            'unit_id' => $item->unit_id,
            'movement_type' => 'adjustment_out',
            'quantity' => $adjustQty,
            'unit_cost' => $item->cost,
            'total_cost' => $adjustQty * $item->cost,
            'reference_type' => 'stock_adjustment',
            'reference_id' => $adjustment->id,
            'reference_no' => $adjustment->adjustment_no,
            'movement_date' => now(),
            'note' => 'Demo adjustment out movement',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Demo Damage Transfer
        |--------------------------------------------------------------------------
        */
        $transfer = StockTransfer::create([
            'company_id' => $company->id,
            'from_branch_id' => $branch->id,
            'to_branch_id' => $branch->id,
            'from_warehouse_id' => $warehouse->id,
            'to_warehouse_id' => $warehouse->id,
            'from_location_id' => $putawayLocation->id,
            'to_location_id' => $damageLocation->id,
            'transfer_no' => DocumentNumber::make(StockTransfer::class, 'transfer_no', 'ST'),
            'transfer_type' => 'damage_transfer',
            'status' => 'received',
            'transfer_date' => now(),
            'approved_at' => now(),
            'dispatched_at' => now(),
            'received_at' => now(),
            'note' => 'Demo transfer from saleable stock to damage zone',
        ]);

        $transferQty = 1;

        StockTransferLine::create([
            'stock_transfer_id' => $transfer->id,
            'item_id' => $item->id,
            'unit_id' => $item->unit_id,
            'quantity_requested' => $transferQty,
            'quantity_dispatched' => $transferQty,
            'quantity_received' => $transferQty,
            'unit_cost' => $item->cost,
            'total_cost' => $transferQty * $item->cost,
            'status' => 'received',
        ]);

        $sourceBalance = StockBalance::where('branch_id', $branch->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('stock_location_id', $putawayLocation->id)
            ->where('item_id', $item->id)
            ->first();

        if ($sourceBalance) {
            $sourceBalance->update([
                'quantity_on_hand' => $sourceBalance->quantity_on_hand - $transferQty,
                'quantity_available' => $sourceBalance->quantity_available - $transferQty,
            ]);
        }

        $targetBalance = StockBalance::firstOrCreate(
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouse->id,
                'stock_location_id' => $damageLocation->id,
                'item_id' => $item->id,
            ],
            [
                'unit_id' => $item->unit_id,
                'quantity_on_hand' => 0,
                'quantity_reserved' => 0,
                'quantity_available' => 0,
                'average_cost' => $item->cost,
            ]
        );

        $targetBalance->update([
            'quantity_on_hand' => $targetBalance->quantity_on_hand + $transferQty,
            'quantity_available' => $targetBalance->quantity_available + $transferQty,
        ]);

        StockMovement::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'from_location_id' => $putawayLocation->id,
            'to_location_id' => $damageLocation->id,
            'item_id' => $item->id,
            'unit_id' => $item->unit_id,
            'movement_type' => 'damage_transfer',
            'quantity' => $transferQty,
            'unit_cost' => $item->cost,
            'total_cost' => $transferQty * $item->cost,
            'reference_type' => 'stock_transfer',
            'reference_id' => $transfer->id,
            'reference_no' => $transfer->transfer_no,
            'movement_date' => now(),
            'note' => 'Demo damage transfer movement',
        ]);
    }
}
