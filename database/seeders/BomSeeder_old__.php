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
        Branch::with('company')->orderBy('id')->get()->each(function (Branch $branch) {
            $company = $branch->company;

            // Create Fried Rice BOM
            $this->createFriedRiceBom($company, $branch);

            // Create Hot Coffee BOM
            $this->createHotCoffeeBom($company, $branch);
        });
    }

    private function createFriedRiceBom(Company $company, Branch $branch): void
    {
        // Ensure required units exist
        $kg = $this->ensureUnit('KG', 'Kilogram', 'package');
        $pcs = $this->ensureUnit('PCS', 'Piece', 'count');

        if (! $kg || ! $pcs) {
            return;
        }

        // Ensure required items exist
        $rice = $this->ensureItem($company, 'RM-RICE', 'Rice', $kg, 'service_material');
        $egg = $this->ensureItem($company, 'RM-EGG', 'Egg', $pcs, 'service_material');

        if (! $rice || ! $egg) {
            return;
        }

        // Find the Fried Rice menu
        $friedRiceMenu = Menu::where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('name', 'Fried Rice')
            ->first();

        if (! $friedRiceMenu) {
            return;
        }

        // Create or retrieve the finished product item
        $friedRiceOutput = Item::firstOrCreate(
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => 'FP-FRIED-RICE-'.$branch->code,
            ],
            [
                'unit_id' => $pcs->id,
                'name' => 'Fried Rice ('.$branch->name.')',
                'item_type' => 'finished_product',
                'cost' => 0.35,
                'is_stockable' => true,
                'is_active' => true,
            ]
        );

        // Create the BOM header
        $bom = BomHeader::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'output_item_id' => $friedRiceOutput->id,
            'bom_no' => DocumentNumber::make(BomHeader::class, 'bom_no', 'BM'),
            'name' => 'Fried Rice Standard BOM for '.$branch->name,
            'output_quantity' => 1,
            'status' => 'active',
        ]);

        // Link the menu to the BOM and item
        $friedRiceMenu->update([
            'item_id' => $friedRiceOutput->id,
            'bom_header_id' => $bom->id,
        ]);

        // Create BOM lines
        BomLine::create([
            'bom_header_id' => $bom->id,
            'component_item_id' => $rice->id,
            'unit_id' => $kg->id,
            'quantity' => 0.25,
            'wastage_percent' => 2,
            'estimated_cost' => 0.20,
            'note' => 'Rice base for fried rice.',
        ]);

        BomLine::create([
            'bom_header_id' => $bom->id,
            'component_item_id' => $egg->id,
            'unit_id' => $pcs->id,
            'quantity' => 1,
            'wastage_percent' => 0,
            'estimated_cost' => 0.15,
            'note' => 'Egg for flavor and binding.',
        ]);
    }

    private function createHotCoffeeBom(Company $company, Branch $branch): void
    {
        // Ensure required units exist
        $g = $this->ensureUnit('G', 'Gram', 'package');
        $ml = $this->ensureUnit('ML', 'Milliliter', 'package');
        $pcs = $this->ensureUnit('PCS', 'Piece', 'count');

        if (! $g || ! $ml || ! $pcs) {
            return;
        }

        // Ensure required items exist
        $coffee = $this->ensureItem($company, 'RM-COFFEE-BEAN', 'Coffee Bean', $g, 'service_material');
        $milk = $this->ensureItem($company, 'RM-MILK', 'Milk', $ml, 'service_material');
        $sugar = $this->ensureItem($company, 'RM-SUGAR', 'Sugar', $g, 'service_material');
        $paperCup = $this->ensureItem($company, 'PKG-PAPER-CUP', 'Paper Cup', $pcs, 'service_material');

        if (! $coffee || ! $milk || ! $sugar || ! $paperCup) {
            return;
        }

        // Find the Hot Coffee menu
        $hotCoffeeMenu = Menu::where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('name', 'Hot Coffee')
            ->first();

        if (! $hotCoffeeMenu) {
            return;
        }

        // Create or retrieve the finished product item
        $coffeeOutput = Item::firstOrCreate(
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => 'FP-HOT-COFFEE-'.$branch->code,
            ],
            [
                'unit_id' => $pcs->id,
                'name' => 'Hot Coffee ('.$branch->name.')',
                'item_type' => 'finished_product',
                'cost' => 0.32,
                'is_stockable' => true,
                'is_active' => true,
            ]
        );

        // Create the BOM header
        $coffeeBom = BomHeader::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'output_item_id' => $coffeeOutput->id,
            'bom_no' => DocumentNumber::make(BomHeader::class, 'bom_no', 'BM'),
            'name' => 'Hot Coffee Standard BOM for '.$branch->name,
            'output_quantity' => 1,
            'status' => 'active',
        ]);

        // Link the menu to the BOM and item
        $hotCoffeeMenu->update([
            'item_id' => $coffeeOutput->id,
            'bom_header_id' => $coffeeBom->id,
        ]);

        // Create BOM lines
        BomLine::create([
            'bom_header_id' => $coffeeBom->id,
            'component_item_id' => $coffee->id,
            'unit_id' => $g->id,
            'quantity' => 18,
            'wastage_percent' => 0,
            'estimated_cost' => 0.1710,
            'note' => 'Ground coffee portion.',
        ]);

        BomLine::create([
            'bom_header_id' => $coffeeBom->id,
            'component_item_id' => $milk->id,
            'unit_id' => $ml->id,
            'quantity' => 80,
            'wastage_percent' => 1,
            'estimated_cost' => 0.0960,
            'note' => 'Milk serving portion.',
        ]);

        BomLine::create([
            'bom_header_id' => $coffeeBom->id,
            'component_item_id' => $sugar->id,
            'unit_id' => $g->id,
            'quantity' => 8,
            'wastage_percent' => 0,
            'estimated_cost' => 0.0076,
            'note' => 'Default sweetener portion.',
        ]);

        BomLine::create([
            'bom_header_id' => $coffeeBom->id,
            'component_item_id' => $paperCup->id,
            'unit_id' => $pcs->id,
            'quantity' => 1,
            'wastage_percent' => 0,
            'estimated_cost' => 0.0400,
            'note' => 'Disposable cup.',
        ]);
    }

    private function ensureUnit(string $code, string $name, string $category): ?Unit
    {
        return Unit::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'code' => $code,
                'category' => $category,
                'unit_type' => 'reference',
                'base_unit_id' => null,
                'base_quantity' => 1,
                'is_active' => true,
            ]
        );
    }

    private function ensureItem(Company $company, string $code, string $name, Unit $unit, string $itemType): ?Item
    {
        return Item::firstOrCreate(
            [
                'company_id' => $company->id,
                'code' => $code,
            ],
            [
                'unit_id' => $unit->id,
                'name' => $name,
                'item_type' => $itemType,
                'cost' => 0,
                'is_stockable' => true,
                'is_active' => true,
            ]
        );
    }
}
