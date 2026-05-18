<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Menu;
use App\Models\MenuPrice;
use App\Models\MenuPriceList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MenuPriceListController
{
    public function index(Request $request): Response
    {
        [$companyId] = $this->scope($request);

        $priceLists = MenuPriceList::query()
            ->with(['branch:id,name,code'])
            ->withCount('prices')
            ->where('company_id', $companyId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get()
            ->map(fn (MenuPriceList $priceList): array => [
                'id' => $priceList->id,
                'code' => $priceList->code,
                'name' => $priceList->name,
                'branchId' => $priceList->branch_id,
                'branch' => $priceList->branch?->name ?? 'All Branches',
                'description' => $priceList->description,
                'isDefault' => $priceList->is_default,
                'isActive' => $priceList->is_active,
                'pricesCount' => $priceList->prices_count,
            ]);

        $prices = MenuPrice::query()
            ->with([
                'menu:id,name,code,base_price,company_id,branch_id,menu_category_id',
                'menu.menuCategory:id,name',
                'branch:id,name,code',
                'priceList:id,name,code',
            ])
            ->whereHas('menu', fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('menu_price_list_id')
            ->orderBy('price_name')
            ->get()
            ->map(fn (MenuPrice $price): array => [
                'id' => $price->id,
                'priceListId' => $price->menu_price_list_id,
                'priceList' => $price->priceList?->name ?? 'Unassigned',
                'menuId' => $price->menu_id,
                'menuCode' => $price->menu?->code,
                'menuName' => $price->menu?->name ?? 'Unknown Menu',
                'category' => $price->menu?->menuCategory?->name ?? 'Uncategorized',
                'branchId' => $price->branch_id,
                'branch' => $price->branch?->name ?? 'All Branches',
                'priceName' => $price->price_name,
                'price' => number_format((float) $price->price, 2, '.', ''),
                'isDefault' => $price->is_default,
                'isActive' => $price->is_active,
            ]);

        return Inertia::render('MasterData/MenuPriceList', [
            'priceLists' => $priceLists,
            'prices' => $prices,
            'menus' => Menu::query()
                ->with(['menuCategory:id,name', 'branch:id,name,code'])
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn (Menu $menu): array => [
                    'id' => $menu->id,
                    'code' => $menu->code,
                    'name' => $menu->name,
                    'category' => $menu->menuCategory?->name ?? 'Uncategorized',
                    'branch' => $menu->branch?->name ?? 'All Branches',
                    'basePrice' => number_format((float) $menu->base_price, 2, '.', ''),
                ]),
            'branchOptions' => Branch::query()
                ->where('company_id', $companyId)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
        ]);
    }

    public function store(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('menu_price_lists', 'code')
                    ->where('company_id', $companyId)
                    ->where('branch_id', $request->input('branch_id') ?: $branchId),
            ],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'description' => ['nullable', 'string'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($data, $companyId, $branchId) {
            $scopedBranchId = $data['branch_id'] ?? $branchId;

            if ($data['is_default'] ?? false) {
                MenuPriceList::query()
                    ->where('company_id', $companyId)
                    ->where('branch_id', $scopedBranchId)
                    ->update(['is_default' => false]);
            }

            MenuPriceList::create([
                ...$data,
                'company_id' => $companyId,
                'branch_id' => $scopedBranchId,
                'is_default' => $data['is_default'] ?? false,
                'is_active' => $data['is_active'] ?? true,
            ]);
        });

        return back()->with('success', 'Menu price list has been created.');
    }

    public function update(Request $request, MenuPriceList $priceList)
    {
        [$companyId, $branchId] = $this->scope($request);

        abort_if($priceList->company_id !== $companyId, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('menu_price_lists', 'code')
                    ->where('company_id', $companyId)
                    ->where('branch_id', $request->input('branch_id') ?: $branchId)
                    ->ignore($priceList->id),
            ],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'description' => ['nullable', 'string'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($data, $priceList, $companyId, $branchId) {
            $scopedBranchId = $data['branch_id'] ?? $branchId;
            $isDefault = (bool) ($data['is_default'] ?? false);
            $isActive = (bool) ($data['is_active'] ?? false);

            if ($isDefault) {
                MenuPriceList::query()
                    ->where('company_id', $companyId)
                    ->where('branch_id', $scopedBranchId)
                    ->whereKeyNot($priceList->id)
                    ->update(['is_default' => false]);
            }

            $priceList->update([
                ...$data,
                'branch_id' => $scopedBranchId,
                'is_default' => $isActive ? $isDefault : false,
                'is_active' => $isActive,
            ]);

            $priceList->prices()->update([
                'price_name' => $priceList->name,
                'is_active' => $isActive,
                'is_default' => $priceList->is_default,
            ]);
        });

        return back()->with('success', 'Menu price list has been updated.');
    }

    public function storePrice(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $request->validate([
            'menu_price_list_id' => ['required', Rule::exists('menu_price_lists', 'id')->where('company_id', $companyId)],
            'menu_id' => ['required', Rule::exists('menus', 'id')->where('company_id', $companyId)],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'price' => ['required', 'numeric', 'min:0'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $priceList = MenuPriceList::query()
            ->where('company_id', $companyId)
            ->findOrFail($data['menu_price_list_id']);

        $scopedBranchId = $data['branch_id'] ?? $priceList->branch_id ?? $branchId;

        if ($data['is_default'] ?? false) {
            MenuPrice::query()
                ->where('menu_id', $data['menu_id'])
                ->where('branch_id', $scopedBranchId)
                ->update(['is_default' => false]);
        }

        MenuPrice::updateOrCreate(
            [
                'menu_price_list_id' => $priceList->id,
                'menu_id' => $data['menu_id'],
                'branch_id' => $scopedBranchId,
            ],
            [
                'price_name' => $priceList->name,
                'price' => $data['price'],
                'is_default' => $data['is_default'] ?? $priceList->is_default,
                'is_active' => $data['is_active'] ?? true,
            ]
        );

        return back()->with('success', 'Menu price has been saved.');
    }

    public function updatePrice(Request $request, MenuPrice $price)
    {
        [$companyId, $branchId] = $this->scope($request);

        $price->load('menu');

        abort_if($price->menu?->company_id !== $companyId, 404);

        $data = $request->validate([
            'menu_price_list_id' => ['required', Rule::exists('menu_price_lists', 'id')->where('company_id', $companyId)],
            'menu_id' => ['required', Rule::exists('menus', 'id')->where('company_id', $companyId)],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'price' => ['required', 'numeric', 'min:0'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $priceList = MenuPriceList::query()
            ->where('company_id', $companyId)
            ->findOrFail($data['menu_price_list_id']);
        $scopedBranchId = $data['branch_id'] ?? $priceList->branch_id ?? $branchId;

        if ($data['is_default'] ?? false) {
            MenuPrice::query()
                ->where('menu_id', $data['menu_id'])
                ->where('branch_id', $scopedBranchId)
                ->whereKeyNot($price->id)
                ->update(['is_default' => false]);
        }

        $price->update([
            'menu_price_list_id' => $priceList->id,
            'menu_id' => $data['menu_id'],
            'branch_id' => $scopedBranchId,
            'price_name' => $priceList->name,
            'price' => $data['price'],
            'is_default' => $data['is_default'] ?? $priceList->is_default,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return back()->with('success', 'Menu price has been updated.');
    }

    private function scope(Request $request): array
    {
        $companyId = $request->user()?->company_id ?? Company::query()->value('id');
        $branchId = $request->user()?->branch_id ?? Branch::query()->where('company_id', $companyId)->value('id');

        abort_if(! $companyId, 422, 'No company is available.');
        abort_if(! $branchId, 422, 'No branch is available.');

        return [(int) $companyId, (int) $branchId];
    }
}
