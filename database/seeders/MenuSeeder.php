<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\ItemUnitConversion;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuPrice;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        if (! Company::exists() || ! Branch::exists()) {
            return;
        }

        $units = Unit::whereIn('code', [
            'BTL',
            'CASE12',
            'G',
            'KG',
            'L',
            'ML',
            'PCS',
            'BOX',
            'TRAY',
        ])->get()->keyBy('code');

        if ($units->count() < 9) {
            return;
        }

        Branch::with('company')->orderBy('id')->get()->each(function (Branch $branch) use ($units) {
            $company = $branch->company;
            $isFirstBranch = $branch->is(Branch::orderBy('id')->first());

            $this->seedStockItems($company, $branch, $units, $isFirstBranch);

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

    /**
     * @param  Collection<string, Unit>  $units
     */
    private function seedStockItems(Company $company, Branch $branch, $units, bool $useLegacyCodes): void
    {
        $suffix = $useLegacyCodes ? '' : '-'.$branch->code;

        $rice = Item::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'unit_id' => $units['KG']->id,
            'name' => 'Rice',
            'code' => 'RM-RICE'.$suffix,
            'item_type' => 'raw_material',
            'cost' => 0.80,
        ]);

        $egg = Item::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'unit_id' => $units['PCS']->id,
            'name' => 'Egg',
            'code' => 'RM-EGG'.$suffix,
            'item_type' => 'ingredient',
            'cost' => 0.15,
        ]);

        $cola = Item::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'unit_id' => $units['BTL']->id,
            'name' => 'Coca Cola Bottle',
            'code' => 'DRK-COKE'.$suffix,
            'item_type' => 'drink',
            'cost' => 0.45,
        ]);

        $coffee = Item::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'unit_id' => $units['KG']->id,
            'name' => 'Coffee Beans',
            'code' => 'RM-COFFEE-BEAN'.$suffix,
            'item_type' => 'ingredient',
            'cost' => 9.50,
        ]);

        $milk = Item::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'unit_id' => $units['L']->id,
            'name' => 'Fresh Milk',
            'code' => 'RM-MILK'.$suffix,
            'item_type' => 'ingredient',
            'cost' => 1.20,
        ]);

        $sugar = Item::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'unit_id' => $units['KG']->id,
            'name' => 'Sugar',
            'code' => 'RM-SUGAR'.$suffix,
            'item_type' => 'ingredient',
            'cost' => 0.95,
        ]);

        $paperCup = Item::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'unit_id' => $units['PCS']->id,
            'name' => 'Paper Cup',
            'code' => 'PKG-PAPER-CUP'.$suffix,
            'item_type' => 'packaging',
            'cost' => 0.04,
        ]);

        $this->seedItemUnitConversions($company, $branch, $units, [
            [$egg, 'TRAY', 'PCS', 30, true, false],
            [$cola, 'CASE12', 'BTL', 12, true, true],
            [$coffee, 'KG', 'G', 1000, false, false],
            [$coffee, 'G', 'KG', 0.001, false, false],
            [$milk, 'L', 'ML', 1000, false, false],
            [$milk, 'ML', 'L', 0.001, false, false],
            [$sugar, 'KG', 'G', 1000, false, false],
            [$sugar, 'G', 'KG', 0.001, false, false],
            [$paperCup, 'BOX', 'PCS', 50, true, false],
            [$rice, 'KG', 'G', 1000, false, false],
            [$rice, 'G', 'KG', 0.001, false, false],
        ]);
    }

    /**
     * @param  Collection<string, Unit>  $units
     * @param  array<int, array{0: Item, 1: string, 2: string, 3: float|int, 4: bool, 5: bool}>  $conversions
     */
    private function seedItemUnitConversions(Company $company, Branch $branch, $units, array $conversions): void
    {
        foreach ($conversions as [$item, $fromCode, $toCode, $factor, $purchaseDefault, $salesDefault]) {
            ItemUnitConversion::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'item_id' => $item->id,
                'from_unit_id' => $units[$fromCode]->id,
                'to_unit_id' => $units[$toCode]->id,
                'factor' => $factor,
                'is_purchase_default' => $purchaseDefault,
                'is_sales_default' => $salesDefault,
                'is_inventory_default' => $fromCode === $item->unit->code,
                'description' => "1 {$fromCode} = {$factor} {$toCode} for {$item->name}.",
            ]);
        }
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
