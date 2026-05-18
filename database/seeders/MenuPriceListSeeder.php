<?php

namespace Database\Seeders;

use App\Models\Branch;
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
                $local = MenuPriceList::create([
                    'company_id' => $branch->company_id,
                    'branch_id' => $branch->id,
                    'name' => 'Local PriceList',
                    'code' => 'LOCAL',
                    'description' => 'Default menu price list for local customers.',
                    'is_default' => true,
                    'is_active' => true,
                ]);

                $foreigner = MenuPriceList::create([
                    'company_id' => $branch->company_id,
                    'branch_id' => $branch->id,
                    'name' => 'ForeignerPricelist',
                    'code' => 'FOREIGNER',
                    'description' => 'Alternative menu price list for foreign customers.',
                    'is_default' => false,
                    'is_active' => true,
                ]);

                Menu::query()
                    ->where('company_id', $branch->company_id)
                    ->where(function ($query) use ($branch): void {
                        $query->whereNull('branch_id')
                            ->orWhere('branch_id', $branch->id);
                    })
                    ->get()
                    ->each(function (Menu $menu) use ($branch, $local, $foreigner): void {
                        MenuPrice::query()
                            ->where('menu_id', $menu->id)
                            ->where('branch_id', $branch->id)
                            ->whereNull('menu_price_list_id')
                            ->update([
                                'menu_price_list_id' => $local->id,
                                'price_name' => $local->name,
                                'is_default' => true,
                            ]);

                        MenuPrice::firstOrCreate(
                            [
                                'menu_price_list_id' => $local->id,
                                'menu_id' => $menu->id,
                                'branch_id' => $branch->id,
                            ],
                            [
                                'price_name' => $local->name,
                                'price' => $menu->base_price,
                                'is_default' => true,
                                'is_active' => true,
                            ]
                        );

                        MenuPrice::firstOrCreate(
                            [
                                'menu_price_list_id' => $foreigner->id,
                                'menu_id' => $menu->id,
                                'branch_id' => $branch->id,
                            ],
                            [
                                'price_name' => $foreigner->name,
                                'price' => round(((float) $menu->base_price) * 1.2, 2),
                                'is_default' => false,
                                'is_active' => true,
                            ]
                        );
                    });
            });
    }
}
