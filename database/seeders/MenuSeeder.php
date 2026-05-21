<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuPrice;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        if (! Company::exists() || ! Branch::exists()) {
            return;
        }

        Branch::with('company')->orderBy('id')->get()->each(function (Branch $branch) {
            $company = $branch->company;
            $isFirstBranch = $branch->is(Branch::orderBy('id')->first());

            // Create menu categories
            $appetizersCategory = MenuCategory::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Appetizers',
                'code' => $branch->code.'-APP',
            ]);

            $mainsCategory = MenuCategory::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Mains',
                'code' => $branch->code.'-MAI',
            ]);

            $drinksCategory = MenuCategory::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Drinks',
                'code' => $branch->code.'-DRI',
            ]);

            $dessertsCategory = MenuCategory::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Desserts',
                'code' => $branch->code.'-DES',
            ]);

            $servicesCategory = MenuCategory::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Services',
                'code' => $branch->code.'-SER',
            ]);

            // Appetizers - generic items (no items linked)
            $this->createGenericMenu($branch, $company, $appetizersCategory, 'Spring Rolls', 2.50, $isFirstBranch);
            $this->createGenericMenu($branch, $company, $appetizersCategory, 'Chicken Satay', 3.25, $isFirstBranch);
            $this->createGenericMenu($branch, $company, $appetizersCategory, 'Garlic Bread', 2.00, $isFirstBranch);
            $this->createGenericMenu($branch, $company, $appetizersCategory, 'Fried Wontons', 2.75, $isFirstBranch);
            $this->createGenericMenu($branch, $company, $appetizersCategory, 'Fresh Salad', 3.00, $isFirstBranch);

            // Mains - Fried Rice (will be linked to BOM by BomSeeder)
            $this->createBomMenu($branch, $company, $mainsCategory, 'Fried Rice', 3.50, $isFirstBranch);
            $this->createGenericMenu($branch, $company, $mainsCategory, 'Grilled Chicken', 5.50, $isFirstBranch);
            $this->createGenericMenu($branch, $company, $mainsCategory, 'Beef Lok Lak', 6.25, $isFirstBranch);
            $this->createGenericMenu($branch, $company, $mainsCategory, 'Seafood Noodles', 5.75, $isFirstBranch);
            $this->createGenericMenu($branch, $company, $mainsCategory, 'Vegetable Curry', 4.75, $isFirstBranch);

            // Drinks - link to actual items from ItemSeeder
            $this->createMenuFromItem($branch, $company, $drinksCategory, 'Coca Cola', 1.00, 'BE-001', $isFirstBranch);
            $this->createGenericMenu($branch, $company, $drinksCategory, 'Iced Lemon Tea', 1.25, $isFirstBranch);
            $this->createGenericMenu($branch, $company, $drinksCategory, 'Fresh Orange Juice', 2.00, $isFirstBranch);
            $this->createMenuFromItem($branch, $company, $drinksCategory, 'Mineral Water', 0.75, 'WA-001', $isFirstBranch);
            // Hot Coffee (will be linked to BOM by BomSeeder)
            $this->createBomMenu($branch, $company, $drinksCategory, 'Hot Coffee', 1.50, $isFirstBranch);

            // Desserts - generic items (no items linked)
            $this->createGenericMenu($branch, $company, $dessertsCategory, 'Mango Sticky Rice', 2.75, $isFirstBranch);
            $this->createGenericMenu($branch, $company, $dessertsCategory, 'Chocolate Brownie', 2.50, $isFirstBranch);
            $this->createGenericMenu($branch, $company, $dessertsCategory, 'Coconut Jelly', 1.75, $isFirstBranch);
            $this->createGenericMenu($branch, $company, $dessertsCategory, 'Ice Cream Scoop', 1.50, $isFirstBranch);
            $this->createGenericMenu($branch, $company, $dessertsCategory, 'Banana Fritter', 2.25, $isFirstBranch);

            // Services - service type items
            $this->createServiceMenu($branch, $company, $servicesCategory, 'Special Room Service', 5.00, $isFirstBranch);
            $this->createServiceMenu($branch, $company, $servicesCategory, 'Cake Cutting Service', 3.00, $isFirstBranch);
            $this->createServiceMenu($branch, $company, $servicesCategory, 'Private Dining Setup', 8.00, $isFirstBranch);
            $this->createServiceMenu($branch, $company, $servicesCategory, 'Corkage Service', 4.00, $isFirstBranch);
            $this->createServiceMenu($branch, $company, $servicesCategory, 'Event Cleanup Service', 6.00, $isFirstBranch);
        });
    }

    private function createGenericMenu(Branch $branch, Company $company, $category, string $menuName, float $price, bool $useLegacyCodes): Menu
    {
        $code = $this->menuCode($branch, $menuName, $category->name, 0, $useLegacyCodes);

        return Menu::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'menu_category_id' => $category->id,
            'item_id' => null,
            'bom_header_id' => null,
            'name' => $menuName,
            'code' => $code,
            'menu_type' => 'product',
            'base_price' => $price,
            'description' => $menuName.' for '.$branch->name,
            'is_active' => true,
        ]);
    }

    private function createBomMenu(Branch $branch, Company $company, $category, string $menuName, float $price, bool $useLegacyCodes): Menu
    {
        $code = $this->menuCode($branch, $menuName, $category->name, 0, $useLegacyCodes);

        return Menu::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'menu_category_id' => $category->id,
            'item_id' => null,
            'bom_header_id' => null, // Will be set by BomSeeder
            'name' => $menuName,
            'code' => $code,
            'menu_type' => 'product',
            'base_price' => $price,
            'description' => $menuName.' recipe for '.$branch->name,
            'is_active' => true,
        ]);
    }

    private function createServiceMenu(Branch $branch, Company $company, $category, string $menuName, float $price, bool $useLegacyCodes): Menu
    {
        $code = $this->menuCode($branch, $menuName, $category->name, 0, $useLegacyCodes);

        return Menu::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'menu_category_id' => $category->id,
            'item_id' => null,
            'bom_header_id' => null,
            'name' => $menuName,
            'code' => $code,
            'menu_type' => 'service',
            'base_price' => $price,
            'description' => $menuName.' for '.$branch->name,
            'is_active' => true,
        ]);
    }

    private function createMenuFromItem(Branch $branch, Company $company, $category, string $menuName, float $price, string $itemCode, bool $useLegacyCodes): ?Menu
    {
        $item = Item::where('company_id', $company->id)
            ->where('code', $itemCode)
            ->first();

        if (! $item) {
            return null;
        }

        $code = $this->menuCode($branch, $menuName, $category->name, 0, $useLegacyCodes);

        return Menu::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'menu_category_id' => $category->id,
            'item_id' => $item->id,
            'bom_header_id' => null,
            'name' => $menuName,
            'code' => $code,
            'menu_type' => 'product',
            'base_price' => $price,
            'description' => $menuName.' from '.$item->name.' for '.$branch->name,
            'is_active' => true,
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
