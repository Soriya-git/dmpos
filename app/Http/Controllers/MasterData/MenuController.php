<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuPrice;
use App\Models\MenuPriceList;
use App\Models\BomHeader;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Printer;

class MenuController
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->company_id;

        $menus = Menu::query()
            ->with([
                'branch:id,name,code',
                'menuCategory:id,name,code',
                'defaultPrice:id,menu_id,price',
                'item:id,name,code',
                'bomHeader:id,bom_no,name,output_item_id',
                'bomHeader.outputItem:id,name,code',
                'printer:id,name,code,printer_role,ip_address,port',
            ])
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
                'item' => $menu->item?->name,
                'bom' => $menu->bomHeader?->name,
                'printRoute' => $menu->print_route,
                'printerId' => $menu->printer_id,
                'printer' => $menu->printer?->name,
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
            'itemOptions' => Item::query()
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code'])
                ->map(fn (Item $item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                ]),
            'bomOptions' => BomHeader::query()
                ->with('outputItem:id,name,code')
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
                ->map(fn (BomHeader $bom): array => [
                    'id' => $bom->id,
                    'name' => $bom->name,
                    'code' => $bom->bom_no,
                    'outputItemId' => $bom->output_item_id,
                    'outputItemName' => $bom->outputItem?->name,
                ]),
            'branchOptions' => Branch::query()
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'printerOptions' => Printer::query()
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->where('is_active', true)
                ->orderBy('printer_role')
                ->orderBy('name')
                ->get()
                ->map(fn (Printer $printer): array => [
                    'id' => $printer->id,
                    'name' => $printer->name,
                    'code' => $printer->code,
                    'role' => $printer->printer_role,
                    'connectionType' => $printer->connection_type,
                    'ipAddress' => $printer->ip_address,
                    'port' => $printer->port,
                ]),
        ]);
    }

    public function storeMenu(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'menu_category_id' => ['nullable', Rule::exists('menu_categories', 'id')->where('company_id', $companyId)],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'menu_type' => ['required', Rule::in(['product', 'service'])],
            'base_price' => ['required', 'numeric', 'min:0'],
            'item_id' => ['nullable', Rule::exists('items', 'id')->where('company_id', $companyId)],
            'bom_header_id' => ['nullable', Rule::exists('bom_headers', 'id')->where('company_id', $companyId)],
            'printer_id' => ['nullable', Rule::exists('printers', 'id')->where('company_id', $companyId)],
            'print_route' => ['required', Rule::in(['none', 'stock', 'kitchen', 'bar', 'cashier', 'custom'])],
            'description' => ['nullable', 'string'],
            'is_available' => ['boolean'],
        ]);

        if (! empty($data['bom_header_id']) && empty($data['item_id'])) {
            $data['item_id'] = BomHeader::query()
                ->where('company_id', $companyId)
                ->whereKey($data['bom_header_id'])
                ->value('output_item_id');
        }

        $menu = Menu::create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $data['branch_id'] ?? $branchId,
            'is_active' => true,
            'is_available' => $data['is_available'] ?? true,
            'print_route' => $data['print_route'],
            'printer_id' => $data['printer_id'] ?: null,
        ]);

        $defaultPriceList = $this->defaultPriceList($companyId, (int) $menu->branch_id);

        if ($defaultPriceList) {
            MenuPrice::create([
                'menu_id' => $menu->id,
                'branch_id' => $menu->branch_id,
                'menu_price_list_id' => $defaultPriceList->id,
                'price_name' => $defaultPriceList->name,
                'price' => $menu->base_price,
                'is_default' => true,
                'is_active' => true,
            ]);
        }

        return back()->with('success', 'Menu has been created.');
    }

    public function updateMenu(Request $request, Menu $menu)
    {
        [$companyId] = $this->scope($request);

        abort_if($menu->company_id !== $companyId, 404);

        $data = $request->validate([
            'printer_id' => ['nullable', Rule::exists('printers', 'id')->where('company_id', $companyId)],
            'print_route' => ['required', Rule::in(['none', 'stock', 'kitchen', 'bar', 'cashier', 'custom'])],
        ]);

        $menu->update([
            'print_route' => $data['print_route'],
            'printer_id' => $data['printer_id'] ?: null,
        ]);

        return back()->with('success', 'Menu printer routing has been updated.');
    }

    public function storeCategory(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'description' => ['nullable', 'string'],
        ]);

        MenuCategory::create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $data['branch_id'] ?? $branchId,
            'is_active' => true,
        ]);

        return back()->with('success', 'Menu category has been created.');
    }

    public function storePrice(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $request->validate([
            'menu_id' => ['required', Rule::exists('menus', 'id')->where('company_id', $companyId)],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'price_name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_default' => ['boolean'],
        ]);

        if ($data['is_default'] ?? false) {
            MenuPrice::query()
                ->where('menu_id', $data['menu_id'])
                ->where('branch_id', $data['branch_id'] ?? $branchId)
                ->update(['is_default' => false]);
        }

        MenuPrice::create([
            ...$data,
            'branch_id' => $data['branch_id'] ?? $branchId,
            'menu_price_list_id' => $this->defaultPriceList($companyId, (int) ($data['branch_id'] ?? $branchId))?->id,
            'is_active' => true,
            'is_default' => $data['is_default'] ?? false,
        ]);

        return back()->with('success', 'Menu price has been created.');
    }

    private function scope(Request $request): array
    {
        $companyId = $request->user()?->company_id ?? Company::query()->value('id');
        $branchId = $request->user()?->branch_id ?? Branch::query()->where('company_id', $companyId)->value('id');

        abort_if(! $companyId, 422, 'No company is available.');
        abort_if(! $branchId, 422, 'No branch is available.');

        return [(int) $companyId, (int) $branchId];
    }

    private function defaultPriceList(int $companyId, int $branchId): ?MenuPriceList
    {
        return MenuPriceList::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->where(function ($query) use ($branchId): void {
                $query->where('branch_id', $branchId)
                    ->orWhereNull('branch_id');
            })
            ->orderByRaw('case when branch_id = ? then 0 when branch_id is null then 1 else 2 end', [$branchId])
            ->first();
    }
}
