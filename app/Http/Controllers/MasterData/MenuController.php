<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuPrice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuController
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->company_id;

        $menus = Menu::query()
            ->with(['branch:id,name,code', 'menuCategory:id,name,code', 'defaultPrice:id,menu_id,price'])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('name')
            ->get()
            ->map(fn (Menu $menu): array => [
                'id' => $menu->id,
                'code' => $menu->code ?: 'MENU-'.str_pad((string) $menu->id, 4, '0', STR_PAD_LEFT),
                'name' => $menu->name,
                'category' => $menu->menuCategory?->name ?? 'Uncategorized',
                'branch' => $menu->branch?->name ?? 'All Branches',
                'type' => $menu->menu_type,
                'basePrice' => number_format((float) $menu->base_price, 2),
                'defaultPrice' => number_format((float) ($menu->defaultPrice?->price ?? $menu->base_price), 2),
                'description' => $menu->description,
                'available' => $menu->is_available,
                'status' => $menu->is_active ? 'approved' : 'cancelled',
            ]);

        $categories = MenuCategory::query()
            ->with(['branch:id,name,code'])
            ->withCount('menus')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('name')
            ->get()
            ->map(fn (MenuCategory $category): array => [
                'id' => $category->id,
                'code' => $category->code ?: 'CAT-'.str_pad((string) $category->id, 3, '0', STR_PAD_LEFT),
                'name' => $category->name,
                'branch' => $category->branch?->name ?? 'All Branches',
                'description' => $category->description,
                'menusCount' => $category->menus_count,
                'status' => $category->is_active ? 'approved' : 'cancelled',
            ]);

        $prices = MenuPrice::query()
            ->with(['menu:id,name,code,company_id', 'branch:id,name,code'])
            ->whereHas('menu', fn ($query) => $query->when($companyId, fn ($query) => $query->where('company_id', $companyId)))
            ->orderByDesc('is_default')
            ->orderBy('price_name')
            ->get()
            ->map(fn (MenuPrice $price): array => [
                'id' => $price->id,
                'code' => 'PRICE-'.str_pad((string) $price->id, 4, '0', STR_PAD_LEFT),
                'name' => $price->price_name,
                'menu' => $price->menu?->name ?? 'Unknown Menu',
                'branch' => $price->branch?->name ?? 'All Branches',
                'price' => number_format((float) $price->price, 2),
                'effectiveFrom' => $price->effective_from?->toDateString(),
                'effectiveTo' => $price->effective_to?->toDateString(),
                'isDefault' => $price->is_default,
                'status' => $price->is_active ? 'approved' : 'cancelled',
            ]);

        return Inertia::render('MasterData/Menu', [
            'menus' => $menus,
            'categories' => $categories,
            'prices' => $prices,
        ]);
    }
}
