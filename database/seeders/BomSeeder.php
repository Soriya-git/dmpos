<?php

namespace Database\Seeders;

use App\Models\BomHeader;
use App\Models\BomLine;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\Menu;
use App\Models\Unit;
use App\Support\DocumentNumber;
use Illuminate\Database\Seeder;

class BomSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $branch = Branch::first();

        if (! $company || ! $branch) {
            return;
        }

        $friedRice = Menu::where('code', 'M-FRIED-RICE')->first();
        $hotCoffee = Menu::where('branch_id', $branch->id)
            ->where('name', 'Hot Coffee')
            ->first();
        $rice = Item::where('code', 'RM-RICE')->first();
        $egg = Item::where('code', 'RM-EGG')->first();
        $coffee = Item::where('code', 'RM-COFFEE-BEAN')->first();
        $milk = Item::where('code', 'RM-MILK')->first();
        $sugar = Item::where('code', 'RM-SUGAR')->first();
        $paperCup = Item::where('code', 'PKG-PAPER-CUP')->first();

        $kg = Unit::where('code', 'KG')->first();
        $g = Unit::where('code', 'G')->first();
        $ml = Unit::where('code', 'ML')->first();
        $pcs = Unit::where('code', 'PCS')->first();

        if (! $friedRice || ! $rice || ! $egg || ! $kg || ! $pcs) {
            return;
        }

        $friedRiceOutput = Item::firstOrCreate(
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => 'FP-FRIED-RICE',
            ],
            [
                'unit_id' => $pcs->id,
                'name' => 'Fried Rice',
                'item_type' => 'finished_product',
                'cost' => 0.35,
            ],
        );

        $bom = BomHeader::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'output_item_id' => $friedRiceOutput->id,
            'bom_no' => DocumentNumber::make(BomHeader::class, 'bom_no', 'BM'),
            'name' => 'Fried Rice Standard BOM',
            'output_quantity' => 1,
            'status' => 'active',
        ]);

        $friedRice->update([
            'item_id' => $friedRiceOutput->id,
            'bom_header_id' => $bom->id,
        ]);

        BomLine::create([
            'bom_header_id' => $bom->id,
            'component_item_id' => $rice->id,
            'unit_id' => $kg->id,
            'quantity' => 0.25,
            'wastage_percent' => 2,
            'estimated_cost' => 0.20,
        ]);

        BomLine::create([
            'bom_header_id' => $bom->id,
            'component_item_id' => $egg->id,
            'unit_id' => $pcs->id,
            'quantity' => 1,
            'wastage_percent' => 0,
            'estimated_cost' => 0.15,
        ]);

        if (! $hotCoffee || ! $coffee || ! $milk || ! $sugar || ! $paperCup || ! $g || ! $ml) {
            return;
        }

        $coffeeOutput = Item::firstOrCreate(
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => 'FP-HOT-COFFEE',
            ],
            [
                'unit_id' => $pcs->id,
                'name' => 'Hot Coffee',
                'item_type' => 'finished_product',
                'cost' => 0.32,
            ],
        );

        $coffeeBom = BomHeader::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'output_item_id' => $coffeeOutput->id,
            'bom_no' => DocumentNumber::make(BomHeader::class, 'bom_no', 'BM'),
            'name' => 'Hot Coffee Standard BOM',
            'output_quantity' => 1,
            'status' => 'active',
        ]);

        $hotCoffee->update([
            'item_id' => $coffeeOutput->id,
            'bom_header_id' => $coffeeBom->id,
        ]);

        foreach ([
            [$coffee, $g, 18, 0, 0.1710, 'Ground coffee portion.'],
            [$milk, $ml, 80, 1, 0.0960, 'Milk serving portion.'],
            [$sugar, $g, 8, 0, 0.0076, 'Default sweetener portion.'],
            [$paperCup, $pcs, 1, 0, 0.0400, 'Disposable cup.'],
        ] as [$item, $unit, $quantity, $wastage, $cost, $note]) {
            BomLine::create([
                'bom_header_id' => $coffeeBom->id,
                'component_item_id' => $item->id,
                'unit_id' => $unit->id,
                'quantity' => $quantity,
                'wastage_percent' => $wastage,
                'estimated_cost' => $cost,
                'note' => $note,
            ]);
        }
    }
}
