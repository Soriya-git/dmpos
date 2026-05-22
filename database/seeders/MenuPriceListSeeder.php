<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Item;
use App\Models\Menu;
use App\Models\MenuPrice;
use App\Models\MenuPriceList;
use Illuminate\Database\Seeder;

class MenuPriceListSeeder extends Seeder
{
    public function run(): void
    {
        Branch::query()
            ->with('company')
            ->orderBy('id')
            ->get()
            ->each(function (Branch $branch): void {
                $local = $this->priceListFor(
                    branch: $branch,
                    name: 'Local PriceList',
                    code: 'LOCAL',
                    description: 'Default menu price list for local customers.',
                    isDefault: true
                );

                $foreigner = $this->priceListFor(
                    branch: $branch,
                    name: 'ForeignerPricelist',
                    code: 'FOREIGNER',
                    description: 'Alternative menu price list for foreign customers.',
                    isDefault: false
                );

                Menu::query()
                    ->with('item')
                    ->where('company_id', $branch->company_id)
                    ->where(function ($query) use ($branch): void {
                        $query->whereNull('branch_id')
                            ->orWhere('branch_id', $branch->id);
                    })
                    ->orderBy('id')
                    ->get()
                    ->each(function (Menu $menu) use ($branch, $local, $foreigner): void {
                        $basePrice = $this->defaultPriceFor($menu);

                        if ((float) $menu->base_price !== $basePrice) {
                            $menu->update(['base_price' => $basePrice]);
                        }

                        $this->menuPriceFor($branch, $menu, $local, $basePrice, true);
                        $this->menuPriceFor($branch, $menu, $foreigner, round($basePrice * 1.2, 2), false);
                    });
            });
    }

    private function priceListFor(
        Branch $branch,
        string $name,
        string $code,
        string $description,
        bool $isDefault
    ): MenuPriceList {
        if ($isDefault) {
            MenuPriceList::query()
                ->where('company_id', $branch->company_id)
                ->where('branch_id', $branch->id)
                ->where('code', '!=', $code)
                ->update(['is_default' => false]);
        }

        return MenuPriceList::query()->updateOrCreate(
            [
                'company_id' => $branch->company_id,
                'branch_id' => $branch->id,
                'code' => $code,
            ],
            [
                'name' => $name,
                'description' => $description,
                'is_default' => $isDefault,
                'is_active' => true,
            ]
        );
    }

    private function menuPriceFor(
        Branch $branch,
        Menu $menu,
        MenuPriceList $priceList,
        float $price,
        bool $isDefault
    ): void {
        if ($isDefault) {
            MenuPrice::query()
                ->where('menu_id', $menu->id)
                ->where('branch_id', $branch->id)
                ->where('id', '!=', 0)
                ->update(['is_default' => false]);
        }

        MenuPrice::query()->updateOrCreate(
            [
                'menu_price_list_id' => $priceList->id,
                'menu_id' => $menu->id,
                'branch_id' => $branch->id,
            ],
            [
                'price_name' => $priceList->name,
                'price' => $price,
                'effective_from' => null,
                'effective_to' => null,
                'is_default' => $isDefault,
                'is_active' => true,
            ]
        );
    }

    private function defaultPriceFor(Menu $menu): float
    {
        $item = $menu->item;

        if (! $item instanceof Item) {
            return max(1, (float) $menu->base_price);
        }

        $type = strtolower((string) $item->item_type);
        $code = strtoupper((string) $item->code);
        $name = strtolower((string) $item->name);

        if (str_starts_with($code, 'FP-')) {
            return str_contains($name, 'set') || str_contains($name, 'combo') ? 12 : 4;
        }

        if (str_starts_with($code, 'CH-')) {
            return 120;
        }

        if (str_starts_with($code, 'RW-') || str_starts_with($code, 'WW-') || str_starts_with($code, 'WI-') || str_starts_with($code, 'SK-')) {
            return 45;
        }

        if (str_starts_with($code, 'WH-') || str_starts_with($code, 'CO-') || str_starts_with($code, 'TQ-') || str_starts_with($code, 'SP-')) {
            return 80;
        }

        if (str_starts_with($code, 'BE-') || str_starts_with($code, 'LB-') || str_starts_with($code, 'DB-') || str_starts_with($code, 'SB-') || str_starts_with($code, 'EX-') || str_starts_with($code, 'BS-')) {
            return 3;
        }

        if (str_starts_with($code, 'CI-')) {
            return 5;
        }

        if (str_starts_with($code, 'PC-')) {
            return 2;
        }

        if ($type === 'service_material') {
            return 1;
        }

        if ($type === 'finished_product') {
            return 8;
        }

        if ($type === 'drink') {
            return 2;
        }

        return 1;
    }
}
