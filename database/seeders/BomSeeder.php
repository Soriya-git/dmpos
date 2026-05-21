<?php

namespace Database\Seeders;

use App\Models\BomHeader;
use App\Models\BomLine;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\Menu;
use App\Models\Unit;
use App\Support\DocumentNumber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BomSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('me_templates/bom_master.csv');

        if (! is_file($path)) {
            $this->command?->warn("BOM CSV file not found: {$path}");

            return;
        }

        $rows = $this->csvRows($path);

        if ($rows === []) {
            $this->command?->warn('BOM CSV file is empty or invalid.');

            return;
        }

        $groups = collect($rows)->groupBy(function (array $row): string {
            return implode('|', [
                $row['branch_code'] ?? '',
                $row['bom_no'] ?? '',
                $row['bom_name'] ?? '',
                $row['output_item_code'] ?? '',
            ]);
        });

        DB::transaction(function () use ($groups): void {
            foreach ($groups as $bomRows) {
                $first = $bomRows->first();

                if (! $first) {
                    continue;
                }

                $branchCode = trim((string) ($first['branch_code'] ?? ''));
                $branch = $this->branchFor($branchCode);

                if (! $branch) {
                    $this->command?->warn("Skipped BOM because branch_code was not found: {$branchCode}");

                    continue;
                }

                $company = $branch->company;

                if (! $company) {
                    $this->command?->warn("Skipped BOM because company was not found for branch: {$branchCode}");

                    continue;
                }

                $outputItemCode = trim((string) ($first['output_item_code'] ?? ''));
                $outputItemName = trim((string) ($first['output_item_name'] ?? ''));
                $outputUnitName = trim((string) ($first['output_unit'] ?? 'Set'));
                $bomName = trim((string) ($first['bom_name'] ?? ''));
                $bomNo = trim((string) ($first['bom_no'] ?? ''));

                if ($outputItemCode === '' || $outputItemName === '' || $bomName === '') {
                    $this->command?->warn('Skipped BOM because output_item_code, output_item_name, or bom_name is missing.');

                    continue;
                }

                $outputUnit = $this->unitFor($outputUnitName);

                $outputItem = $this->itemFor(
                    company: $company,
                    branch: null,
                    code: $outputItemCode,
                    name: $outputItemName,
                    unit: $outputUnit,
                    itemType: 'finished_product',
                    isStockable: true
                );

                $bomHeader = $this->bomHeaderFor(
                    company: $company,
                    branch: $branch,
                    outputItem: $outputItem,
                    row: $first
                );

                BomLine::query()
                    ->where('bom_header_id', $bomHeader->id)
                    ->delete();

                foreach ($bomRows as $lineRow) {
                    $this->createBomLine($company, $bomHeader, $lineRow);
                }

                $menuName = trim((string) ($first['menu_name'] ?? ''));

                if ($menuName !== '') {
                    $this->linkMenuToBom(
                        company: $company,
                        branch: $branch,
                        menuName: $menuName,
                        outputItem: $outputItem,
                        bomHeader: $bomHeader
                    );
                }

                $this->command?->info("Seeded BOM: {$bomHeader->bom_no} - {$bomHeader->name}");
            }
        });
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function csvRows(string $path): array
    {
        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);

        $headers = [];
        $rows = [];

        foreach ($file as $row) {
            if (! is_array($row) || $row === [null]) {
                continue;
            }

            if ($headers === []) {
                $headers = array_map(function ($header): string {
                    return Str::of((string) $header)
                        ->replace("\xEF\xBB\xBF", '')
                        ->trim()
                        ->lower()
                        ->replace(' ', '_')
                        ->toString();
                }, $row);

                continue;
            }

            $values = array_pad($row, count($headers), null);

            $record = array_combine(
                $headers,
                array_map(fn ($value): string => trim((string) $value), $values)
            );

            if (! $record) {
                continue;
            }

            if (($record['bom_name'] ?? '') === '' && ($record['output_item_code'] ?? '') === '') {
                continue;
            }

            $rows[] = $record;
        }

        return $rows;
    }

    private function branchFor(string $branchCode): ?Branch
    {
        if ($branchCode === '') {
            return Branch::query()
                ->with('company')
                ->orderBy('id')
                ->first();
        }

        return Branch::query()
            ->with('company')
            ->where('code', $branchCode)
            ->first();
    }

    private function bomHeaderFor(
        Company $company,
        Branch $branch,
        Item $outputItem,
        array $row
    ): BomHeader {
        $bomNo = trim((string) ($row['bom_no'] ?? ''));
        $bomName = trim((string) ($row['bom_name'] ?? ''));
        $status = $this->validStatus((string) ($row['status'] ?? 'active'));

        $data = [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'output_item_id' => $outputItem->id,
            'name' => $bomName,
            'output_quantity' => $this->decimal($row['output_quantity'] ?? 1, 1),
            'status' => $status,
            'effective_from' => $this->dateOrNull($row['effective_from'] ?? null),
            'effective_to' => $this->dateOrNull($row['effective_to'] ?? null),
            'note' => $this->nullableString($row['note'] ?? null),
        ];

        if ($bomNo !== '') {
            return BomHeader::query()->updateOrCreate(
                ['bom_no' => $bomNo],
                $data
            );
        }

        $existing = BomHeader::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('output_item_id', $outputItem->id)
            ->where('name', $bomName)
            ->first();

        if ($existing) {
            $existing->fill($data);
            $existing->save();

            return $existing;
        }

        $data['bom_no'] = DocumentNumber::make(BomHeader::class, 'bom_no', 'BM');

        return BomHeader::query()->create($data);
    }

    private function createBomLine(Company $company, BomHeader $bomHeader, array $row): void
    {
        $componentCode = trim((string) ($row['component_item_code'] ?? ''));
        $componentName = trim((string) ($row['component_item_name'] ?? ''));
        $componentUnitName = trim((string) ($row['component_unit'] ?? ''));

        if ($componentCode === '' || $componentName === '' || $componentUnitName === '') {
            $this->command?->warn("Skipped BOM line for {$bomHeader->name} because component data is incomplete.");

            return;
        }

        $unit = $this->unitFor($componentUnitName);

        $componentItem = $this->itemFor(
            company: $company,
            branch: null,
            code: $componentCode,
            name: $componentName,
            unit: $unit,
            itemType: 'service_material',
            isStockable: true
        );

        BomLine::query()->create([
            'bom_header_id' => $bomHeader->id,
            'component_item_id' => $componentItem->id,
            'unit_id' => $unit->id,
            'quantity' => $this->decimal($row['quantity'] ?? 1, 1),
            'wastage_percent' => $this->decimal($row['wastage_percent'] ?? 0, 0),
            'estimated_cost' => $this->decimal($row['estimated_cost'] ?? 0, 0),
            'note' => $this->nullableString($row['note'] ?? null),
        ]);
    }

    private function itemFor(
        Company $company,
        ?Branch $branch,
        string $code,
        string $name,
        Unit $unit,
        string $itemType,
        bool $isStockable
    ): Item {
        $item = Item::query()
            ->where('company_id', $company->id)
            ->where('code', $code)
            ->where(function ($query) use ($branch): void {
                if ($branch) {
                    $query->where('branch_id', $branch->id)
                        ->orWhereNull('branch_id');
                } else {
                    $query->whereNull('branch_id');
                }
            })
            ->orderByRaw('branch_id IS NULL DESC')
            ->first();

        if ($item) {
            $item->fill([
                'unit_id' => $item->unit_id ?: $unit->id,
                'name' => $item->name ?: $name,
                'item_type' => $item->item_type ?: $itemType,
                'is_stockable' => $item->is_stockable ?? $isStockable,
                'is_active' => true,
            ]);

            $item->save();

            return $item;
        }

        return Item::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch?->id,
            'unit_id' => $unit->id,
            'code' => $code,
            'name' => $name,
            'item_type' => $itemType,
            'cost' => 0,
            'minimum_stock_qty' => 0,
            'is_stockable' => $isStockable,
            'is_active' => true,
            'description' => null,
        ]);
    }

    private function unitFor(string $name): Unit
    {
        $name = trim($name) !== '' ? trim($name) : 'Set';
        $code = $this->unitCode($name);

        $unit = Unit::query()
            ->where('name', $name)
            ->orWhere('code', $code)
            ->first();

        if ($unit) {
            $unit->fill([
                'name' => $unit->name ?: $name,
                'code' => $unit->code ?: $code,
                'is_active' => true,
            ]);

            $unit->save();

            return $unit;
        }

        return Unit::query()->create([
            'name' => $name,
            'code' => $code,
            'category' => $this->unitCategory($name),
            'unit_type' => 'reference',
            'base_unit_id' => null,
            'base_quantity' => 1,
            'description' => null,
            'is_active' => true,
        ]);
    }

    private function linkMenuToBom(
        Company $company,
        Branch $branch,
        string $menuName,
        Item $outputItem,
        BomHeader $bomHeader
    ): void {
        if (! class_exists(Menu::class)) {
            return;
        }

        if (! Schema::hasTable('menus')) {
            return;
        }

        $menu = Menu::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('name', $menuName)
            ->first();

        if (! $menu) {
            return;
        }

        $update = [];

        if (Schema::hasColumn('menus', 'item_id')) {
            $update['item_id'] = $outputItem->id;
        }

        if (Schema::hasColumn('menus', 'bom_header_id')) {
            $update['bom_header_id'] = $bomHeader->id;
        }

        if ($update !== []) {
            $menu->update($update);
        }
    }

    private function unitCode(string $name): string
    {
        $code = Str::of($name)
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return $code !== '' ? $code : 'UNIT';
    }

    private function unitCategory(string $name): string
    {
        $normalized = Str::of($name)->lower()->trim()->toString();

        if (in_array($normalized, [
            'set',
            'piece',
            'pcs',
            'pack',
            'bottle',
            'can',
            'glass',
            'cup',
            'plate',
            'box',
            'unit',
        ], true)) {
            return 'count';
        }

        return 'package';
    }

    private function validStatus(string $status): string
    {
        $status = strtolower(trim($status));

        return in_array($status, ['draft', 'active', 'inactive'], true)
            ? $status
            : 'active';
    }

    private function decimal(mixed $value, float|int $default): float
    {
        if ($value === null || trim((string) $value) === '') {
            return (float) $default;
        }

        return is_numeric($value) ? (float) $value : (float) $default;
    }

    private function dateOrNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return $value;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}