<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        if (! Company::exists() || ! Branch::exists() || ! Item::exists()) {
            return;
        }

        Branch::query()
            ->with('company')
            ->orderBy('id')
            ->get()
            ->each(function (Branch $branch): void {
                $company = $branch->company;

                if (! $company) {
                    return;
                }

                $items = Item::query()
                    ->where('company_id', $company->id)
                    ->whereNull('branch_id')
                    ->where('is_active', true)
                    ->orderBy('item_type')
                    ->orderBy('code')
                    ->get();

                foreach ($items as $item) {
                    $category = $this->categoryFor($company, $branch, $item);
                    $menu = Menu::query()->updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'branch_id' => $branch->id,
                            'code' => $this->menuCode($branch, $item),
                        ],
                        [
                            'menu_category_id' => $category->id,
                            'item_id' => $item->id,
                            'bom_header_id' => null,
                            'name' => $item->name,
                            'menu_type' => $item->item_type === 'service_material' ? 'service' : 'product',
                            'base_price' => 0,
                            'description' => $item->description ?: $item->name,
                            'is_available' => true,
                            'is_active' => true,
                        ]
                    );

                    MenuPrice::query()->updateOrCreate(
                        [
                            'menu_id' => $menu->id,
                            'branch_id' => $branch->id,
                            'price_name' => 'Default Price',
                        ],
                        [
                            'price' => $menu->base_price,
                            'effective_from' => null,
                            'effective_to' => null,
                            'is_default' => true,
                            'is_active' => true,
                        ]
                    );
                }
            });
    }

    private function categoryFor(Company $company, Branch $branch, Item $item): MenuCategory
    {
        [$name, $suffix] = match ($item->item_type) {
            'drink' => ['Drinks', 'DRI'],
            'finished_product' => ['Fruit & Sets', 'FST'],
            'service_material' => ['Consumables & Services', 'SER'],
            default => [$this->categoryNameFromCode((string) $item->code), $this->categorySuffixFromCode((string) $item->code)],
        };

        return MenuCategory::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => "{$branch->code}-{$suffix}",
            ],
            [
                'name' => $name,
                'description' => "{$name} menu category for {$branch->name}",
                'is_active' => true,
            ]
        );
    }

    private function categoryNameFromCode(string $code): string
    {
        $prefix = Str::of($code)->before('-')->upper()->toString();

        return match ($prefix) {
            'CI' => 'Cigarettes',
            'PC' => 'Play Cards',
            'DR' => 'Snacks',
            'MI' => 'Mixers',
            default => 'Other Items',
        };
    }

    private function categorySuffixFromCode(string $code): string
    {
        $prefix = Str::of($code)->before('-')->upper()->toString();

        return match ($prefix) {
            'CI' => 'CIG',
            'PC' => 'PLC',
            'DR' => 'SNK',
            'MI' => 'MIX',
            default => 'OTH',
        };
    }

    private function menuCode(Branch $branch, Item $item): string
    {
        return "{$branch->code}-{$item->code}";
    }
}
