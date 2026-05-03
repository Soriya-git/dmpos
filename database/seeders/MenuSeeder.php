<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuPrice;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $branch = Branch::first();

        if (!$company || !$branch) {
            return;
        }

        $plate = Unit::firstOrCreate(
            ['code' => 'PLT'],
            ['name' => 'Plate']
        );

        $bottle = Unit::firstOrCreate(
            ['code' => 'BTL'],
            ['name' => 'Bottle']
        );

        $kg = Unit::firstOrCreate(
            ['code' => 'KG'],
            ['name' => 'Kilogram']
        );

        $pcs = Unit::firstOrCreate(
            ['code' => 'PCS'],
            ['name' => 'Piece']
        );

        $foodCategory = MenuCategory::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Food',
            'code' => 'FOOD',
        ]);

        $drinkCategory = MenuCategory::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Drink',
            'code' => 'DRINK',
        ]);

        $serviceCategory = MenuCategory::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Service',
            'code' => 'SERVICE',
        ]);

        Item::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'unit_id' => $kg->id,
            'name' => 'Rice',
            'code' => 'RM-RICE',
            'item_type' => 'raw_material',
            'cost' => 0.80,
        ]);

        Item::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'unit_id' => $pcs->id,
            'name' => 'Egg',
            'code' => 'RM-EGG',
            'item_type' => 'ingredient',
            'cost' => 0.15,
        ]);

        Item::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'unit_id' => $bottle->id,
            'name' => 'Coca Cola Bottle',
            'code' => 'DRK-COKE',
            'item_type' => 'drink',
            'cost' => 0.45,
        ]);

        $friedRice = Menu::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'menu_category_id' => $foodCategory->id,
            'name' => 'Fried Rice',
            'code' => 'M-FRIED-RICE',
            'menu_type' => 'product',
            'base_price' => 3.50,
        ]);

        MenuPrice::create([
            'menu_id' => $friedRice->id,
            'branch_id' => $branch->id,
            'price_name' => 'Default Price',
            'price' => 3.50,
            'is_default' => true,
        ]);

        $coke = Menu::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'menu_category_id' => $drinkCategory->id,
            'name' => 'Coca Cola',
            'code' => 'M-COKE',
            'menu_type' => 'product',
            'base_price' => 1.00,
        ]);

        MenuPrice::create([
            'menu_id' => $coke->id,
            'branch_id' => $branch->id,
            'price_name' => 'Default Price',
            'price' => 1.00,
            'is_default' => true,
        ]);

        $specialService = Menu::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'menu_category_id' => $serviceCategory->id,
            'name' => 'Special Room Service',
            'code' => 'S-ROOM-SERVICE',
            'menu_type' => 'service',
            'base_price' => 5.00,
        ]);

        MenuPrice::create([
            'menu_id' => $specialService->id,
            'branch_id' => $branch->id,
            'price_name' => 'Default Price',
            'price' => 5.00,
            'is_default' => true,
        ]);
    }
}