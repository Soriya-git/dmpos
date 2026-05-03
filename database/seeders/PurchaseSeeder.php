<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptLine;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Support\DocumentNumber;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $branch = Branch::first();
        $warehouse = Warehouse::first();
        $putawayLocation = StockLocation::where('location_type', 'putaway')->first();

        if (! $company || ! $branch || ! $warehouse || ! $putawayLocation) {
            return;
        }

        $items = Item::where('company_id', $company->id)
            ->where('is_stockable', true)
            ->take(2)
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $po = PurchaseOrder::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'po_no' => DocumentNumber::make(PurchaseOrder::class, 'po_no', 'PO'),
            'purchase_scope' => 'branch',
            'supplier_name' => 'Demo Supplier',
            'supplier_phone' => '012345678',
            'status' => 'received',
            'order_date' => now()->toDateString(),
            'expected_date' => now()->addDays(1)->toDateString(),
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 0,
            'approved_at' => now(),
            'note' => 'Demo purchase order',
        ]);

        $subtotal = 0;

        foreach ($items as $item) {
            $qty = match ($item->code) {
                'RM-RICE' => 20,
                'RM-EGG' => 50,
                default => 10,
            };

            $lineTotal = $qty * $item->cost;
            $subtotal += $lineTotal;

            PurchaseOrderLine::create([
                'purchase_order_id' => $po->id,
                'branch_id' => $branch->id,
                'item_id' => $item->id,
                'unit_id' => $item->unit_id,
                'quantity_ordered' => $qty,
                'quantity_received' => $qty,
                'quantity_remaining' => 0,
                'unit_cost' => $item->cost,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'line_total' => $lineTotal,
                'status' => 'received',
            ]);
        }

        $po->update([
            'subtotal' => $subtotal,
            'grand_total' => $subtotal,
        ]);

        $receipt = GoodsReceipt::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'purchase_order_id' => $po->id,
            'warehouse_id' => $warehouse->id,
            'stock_location_id' => $putawayLocation->id,
            'receipt_no' => DocumentNumber::make(GoodsReceipt::class, 'receipt_no', 'GR'),
            'status' => 'received',
            'received_at' => now(),
            'note' => 'Demo goods receipt',
        ]);

        foreach ($po->lines as $line) {
            GoodsReceiptLine::create([
                'goods_receipt_id' => $receipt->id,
                'purchase_order_line_id' => $line->id,
                'item_id' => $line->item_id,
                'unit_id' => $line->unit_id,
                'quantity_received' => $line->quantity_received,
                'unit_cost' => $line->unit_cost,
                'total_cost' => $line->quantity_received * $line->unit_cost,
            ]);

            $balance = StockBalance::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'warehouse_id' => $warehouse->id,
                    'stock_location_id' => $putawayLocation->id,
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
                'quantity_on_hand' => $balance->quantity_on_hand + $line->quantity_received,
                'quantity_available' => $balance->quantity_available + $line->quantity_received,
                'average_cost' => $line->unit_cost,
            ]);

            StockMovement::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouse->id,
                'from_location_id' => null,
                'to_location_id' => $putawayLocation->id,
                'item_id' => $line->item_id,
                'unit_id' => $line->unit_id,
                'movement_type' => 'purchase_receipt',
                'quantity' => $line->quantity_received,
                'unit_cost' => $line->unit_cost,
                'total_cost' => $line->quantity_received * $line->unit_cost,
                'reference_type' => 'goods_receipt',
                'reference_id' => $receipt->id,
                'reference_no' => $receipt->receipt_no,
                'movement_date' => now(),
                'note' => 'Demo purchase receipt stock movement',
            ]);
        }
    }
}
