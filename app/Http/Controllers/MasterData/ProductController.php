<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\BomHeader;
use App\Models\BomLine;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\Unit;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        [$companyId] = $this->scope($request);

        return Inertia::render('MasterData/Products', [
            'items' => Item::query()
                ->with(['branches:id,name,code', 'unit:id,name,code'])
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->orderBy('name')
                ->get()
                ->map(fn (Item $item): array => [
                    'id' => $item->id,
                    'code' => $item->code ?: 'ITEM-'.str_pad((string) $item->id, 4, '0', STR_PAD_LEFT),
                    'name' => $item->name,
                    'nameKh' => $item->name_kh,
                    'nameOther' => $item->name_other,
                    'nickname' => $item->nickname,
                    'branchId' => $item->branch_id,
                    'branchIds' => $item->branches->pluck('id')->all(),
                    'branchNicknames' => $item->branches->mapWithKeys(
                        fn (Branch $branch) => [$branch->id => $branch->pivot->nickname]
                    )->all(),
                    'unitId' => $item->unit_id,
                    'itemType' => $item->item_type,
                    'cost' => (string) $item->cost,
                    'minimumStockQty' => (string) $item->minimum_stock_qty,
                    'isStockable' => $item->is_stockable,
                    'description' => $item->description,
                    'category' => str($item->item_type)->replace('_', ' ')->title()->toString(),
                    'primaryUnit' => $item->unit?->code ?? $item->unit?->name ?? '-',
                    'status' => $item->is_active ? 'approved' : 'cancelled',
                ]),
            'bom' => BomHeader::query()
                ->with(['branches:id,name,code', 'outputItem:id,name,code', 'lines.item:id,name,code', 'lines.unit:id,name,code'])
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->orderBy('name')
                ->get()
                ->map(fn (BomHeader $bom): array => [
                    'id' => $bom->id,
                    'code' => $bom->bom_no,
                    'targetProduct' => $bom->outputItem?->name ?? $bom->name,
                    'components' => $bom->lines
                        ->map(fn (BomLine $line): string => trim(($line->item?->name ?? 'Item').' '.number_format((float) $line->quantity, 4).' '.($line->unit?->code ?? '')))
                        ->implode(', '),
                    'branchIds' => $bom->branches->pluck('id')->all(),
                    'status' => $bom->status === 'active' ? 'approved' : ($bom->status === 'inactive' ? 'cancelled' : 'draft'),
                ]),
            'units' => Unit::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Unit $unit): array => [
                    'id' => $unit->id,
                    'code' => $unit->code,
                    'name' => $unit->name,
                    'category' => str($unit->category)->replace('_', ' ')->title()->toString(),
                    'type' => str($unit->unit_type)->replace('_', ' ')->title()->toString(),
                    'ratio' => number_format((float) $unit->base_quantity, 5),
                    'status' => $unit->is_active ? 'approved' : 'cancelled',
                ]),
            'itemOptions' => Item::query()
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'unit_id']),
            'unitOptions' => Unit::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'branchOptions' => Branch::query()
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
        ]);
    }

    public function storeItem(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'name_other' => ['nullable', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'branch_ids' => ['required', 'array', 'min:1'],
            'branch_ids.*' => [Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'branch_nicknames' => ['nullable', 'array'],
            'branch_nicknames.*' => ['nullable', 'string', 'max:255'],
            'unit_id' => ['required', Rule::exists('units', 'id')],
            'item_type' => ['required', Rule::in([
                'raw_material',
                'ingredient',
                'drink',
                'finished_product',
                'packaging',
                'service_material',
                'other',
            ])],
            'cost' => ['required', 'numeric', 'min:0'],
            'minimum_stock_qty' => ['nullable', 'numeric', 'min:0'],
            'is_stockable' => ['boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $item = Item::create([
            ...collect($data)->except(['branch_ids', 'branch_nicknames'])->all(),
            'company_id' => $companyId,
            'branch_id' => null,
            'minimum_stock_qty' => $data['minimum_stock_qty'] ?? 0,
            'is_stockable' => $data['is_stockable'] ?? true,
            'is_active' => true,
        ]);
        $this->syncItemBranches($item, $data);

        return back()->with('success', 'Item has been created.');
    }

    public function updateItem(Request $request, Item $item)
    {
        [$companyId] = $this->scope($request);

        abort_if($item->company_id !== $companyId, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'name_other' => ['nullable', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'branch_ids' => ['required', 'array', 'min:1'],
            'branch_ids.*' => [Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'branch_nicknames' => ['nullable', 'array'],
            'branch_nicknames.*' => ['nullable', 'string', 'max:255'],
            'unit_id' => ['required', Rule::exists('units', 'id')],
            'item_type' => ['required', Rule::in([
                'raw_material', 'ingredient', 'drink', 'finished_product',
                'packaging', 'service_material', 'other',
            ])],
            'cost' => ['required', 'numeric', 'min:0'],
            'minimum_stock_qty' => ['nullable', 'numeric', 'min:0'],
            'is_stockable' => ['boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $item->update(collect($data)->except(['branch_ids', 'branch_nicknames'])->all());
        $this->syncItemBranches($item, $data);

        return back()->with('success', 'Item has been updated.');
    }

    public function storeBom(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bom_no' => ['nullable', 'string', 'max:255', 'unique:bom_headers,bom_no'],
            'branch_ids' => ['required', 'array', 'min:1'],
            'branch_ids.*' => [Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'output_item_id' => ['required', Rule::exists('items', 'id')->where('company_id', $companyId)],
            'output_quantity' => ['required', 'numeric', 'gt:0'],
            'status' => ['required', Rule::in(['draft', 'active', 'inactive'])],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.component_item_id' => ['required', Rule::exists('items', 'id')->where('company_id', $companyId)],
            'lines.*.unit_id' => ['required', Rule::exists('units', 'id')],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.wastage_percent' => ['nullable', 'numeric', 'min:0'],
            'lines.*.estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'lines.*.note' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $companyId, $branchId) {
            $bom = BomHeader::create([
                'company_id' => $companyId,
                'branch_id' => null,
                'output_item_id' => $data['output_item_id'],
                'bom_no' => $data['bom_no'] ?: DocumentNumber::make(BomHeader::class, 'bom_no', 'BM'),
                'name' => $data['name'],
                'output_quantity' => $data['output_quantity'],
                'status' => $data['status'],
                'note' => $data['note'] ?? null,
            ]);
            $bom->branches()->sync($data['branch_ids']);

            foreach ($data['lines'] as $line) {
                $bom->lines()->create([
                    'component_item_id' => $line['component_item_id'],
                    'unit_id' => $line['unit_id'],
                    'quantity' => $line['quantity'],
                    'wastage_percent' => $line['wastage_percent'] ?? 0,
                    'estimated_cost' => $line['estimated_cost'] ?? 0,
                    'note' => $line['note'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'BOM has been created.');
    }

    private function syncItemBranches(Item $item, array $data): void
    {
        $nicknames = $data['branch_nicknames'] ?? [];
        $item->branches()->sync(collect($data['branch_ids'])->mapWithKeys(
            fn ($branchId) => [$branchId => ['nickname' => $nicknames[$branchId] ?? null]]
        )->all());
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
