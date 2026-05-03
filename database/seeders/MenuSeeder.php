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
        if (! Company::exists() || ! Branch::exists()) {
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

        Branch::with('company')->orderBy('id')->get()->each(function (Branch $branch) use ($bottle, $kg, $pcs) {
            $company = $branch->company;
            $isFirstBranch = $branch->is(Branch::orderBy('id')->first());

            $this->seedStockItems($company, $branch, $kg, $pcs, $bottle, $isFirstBranch);

            $menuCategories = [
                'Appetizers' => [
                    ['Spring Rolls', 2.50],
                    ['Chicken Satay', 3.25],
                    ['Garlic Bread', 2.00],
                    ['Fried Wontons', 2.75],
                    ['Fresh Salad', 3.00],
                ],
                'Mains' => [
                    ['Fried Rice', 3.50],
                    ['Grilled Chicken', 5.50],
                    ['Beef Lok Lak', 6.25],
                    ['Seafood Noodles', 5.75],
                    ['Vegetable Curry', 4.75],
                ],
                'Drinks' => [
                    ['Coca Cola', 1.00],
                    ['Iced Lemon Tea', 1.25],
                    ['Fresh Orange Juice', 2.00],
                    ['Mineral Water', 0.75],
                    ['Hot Coffee', 1.50],
                ],
                'Desserts' => [
                    ['Mango Sticky Rice', 2.75],
                    ['Chocolate Brownie', 2.50],
                    ['Coconut Jelly', 1.75],
                    ['Ice Cream Scoop', 1.50],
                    ['Banana Fritter', 2.25],
                ],
                'Services' => [
                    ['Special Room Service', 5.00],
                    ['Cake Cutting Service', 3.00],
                    ['Private Dining Setup', 8.00],
                    ['Corkage Service', 4.00],
                    ['Event Cleanup Service', 6.00],
                ],
            ];

            foreach ($menuCategories as $categoryIndex => $menus) {
                $categoryName = is_string($categoryIndex) ? $categoryIndex : '';
                $categoryCode = strtoupper(substr($categoryName, 0, 3));

                $category = MenuCategory::create([
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'name' => $categoryName,
                    'code' => $branch->code.'-'.$categoryCode,
                ]);

                foreach ($menus as $menuIndex => [$menuName, $price]) {
                    $menuType = $categoryName === 'Services' ? 'service' : 'product';
                    $code = $this->menuCode($branch, $menuName, $categoryName, $menuIndex, $isFirstBranch);

                    $menu = Menu::create([
                        'company_id' => $company->id,
                        'branch_id' => $branch->id,
                        'menu_category_id' => $category->id,
                        'name' => $menuName,
                        'code' => $code,
                        'menu_type' => $menuType,
                        'base_price' => $price,
                        'description' => $menuName.' for '.$branch->name,
                    ]);

                    MenuPrice::create([
                        'menu_id' => $menu->id,
                        'branch_id' => $branch->id,
                        'price_name' => 'Default Price',
                        'price' => $price,
                        'is_default' => true,
                    ]);
                }
            }
        });
    }

    private function seedStockItems(Company $company, Branch $branch, Unit $kg, Unit $pcs, Unit $bottle, bool $useLegacyCodes): void
    {
        $suffix = $useLegacyCodes ? '' : '-'.$branch->code;

        Item::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'unit_id' => $kg->id,
            'name' => 'Rice',
            'code' => 'RM-RICE'.$suffix,
            'item_type' => 'raw_material',
            'cost' => 0.80,
        ]);

        Item::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'unit_id' => $pcs->id,
            'name' => 'Egg',
            'code' => 'RM-EGG'.$suffix,
            'item_type' => 'ingredient',
            'cost' => 0.15,
        ]);

        Item::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'unit_id' => $bottle->id,
            'name' => 'Coca Cola Bottle',
            'code' => 'DRK-COKE'.$suffix,
            'item_type' => 'drink',
            'cost' => 0.45,
        ]);
    }

    private function menuCode(Branch $branch, string $menuName, string $categoryName, int $menuIndex, bool $useLegacyCodes): string
    {
        if ($useLegacyCodes && $menuName === 'Fried Rice') {
            return 'M-FRIED-RICE';
        }

        if ($useLegacyCodes && $menuName === 'Coca Cola') {
            return 'M-COKE';
        }

        if ($useLegacyCodes && $menuName === 'Special Room Service') {
            return 'S-ROOM-SERVICE';
        }

        $categoryCode = strtoupper(substr($categoryName, 0, 3));

        return $branch->code.'-'.$categoryCode.'-'.str_pad((string) ($menuIndex + 1), 2, '0', STR_PAD_LEFT);
    }
}
