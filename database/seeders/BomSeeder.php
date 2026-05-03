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
        $rice = Item::where('code', 'RM-RICE')->first();
        $egg = Item::where('code', 'RM-EGG')->first();

        $kg = Unit::where('code', 'KG')->first();
        $pcs = Unit::where('code', 'PCS')->first();

        if (! $friedRice || ! $rice || ! $egg || ! $kg || ! $pcs) {
            return;
        }

        $bom = BomHeader::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'menu_id' => $friedRice->id,
            'bom_no' => DocumentNumber::make(BomHeader::class, 'bom_no', 'BM'),
            'name' => 'Fried Rice Standard BOM',
            'output_quantity' => 1,
            'status' => 'active',
        ]);

        BomLine::create([
            'bom_header_id' => $bom->id,
            'item_id' => $rice->id,
            'unit_id' => $kg->id,
            'quantity' => 0.25,
            'wastage_percent' => 2,
            'estimated_cost' => 0.20,
        ]);

        BomLine::create([
            'bom_header_id' => $bom->id,
            'item_id' => $egg->id,
            'unit_id' => $pcs->id,
            'quantity' => 1,
            'wastage_percent' => 0,
            'estimated_cost' => 0.15,
        ]);
    }
}
