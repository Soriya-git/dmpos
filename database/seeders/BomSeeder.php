<?php

namespace Database\Seeders;

use App\Models\BomHeader;
use App\Models\BomLine;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\Menu;
use App\Support\DocumentNumber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

        $groups = collect($rows)->groupBy(fn (array $row): string => implode('|', [
            $row['branch_code'] ?? '',
            $row['bom_no'] ?? '',
            $row['bom_name'] ?? '',
            $row['output_item_code'] ?? '',
        ]));

        DB::transaction(function () use ($groups): void {
            foreach ($groups as $bomRows) {
                $this->seedBom($bomRows);
            }
        });
    }

    /**
     * @param  Collection<int, array<string, string>>  $bomRows
     */
    private function seedBom(Collection $bomRows): void
    {
        $first = $bomRows->first();

        if (! $first) {
            return;
        }

        $branch = $this->branchFor((string) ($first['branch_code'] ?? ''));

        if (! $branch || ! $branch->company) {
            $this->command?->warn('Skipped BOM because branch/company was not found.');

            return;
        }

        $company = $branch->company;
        $outputItemCode = trim((string) ($first['output_item_code'] ?? ''));
        $bomName = trim((string) ($first['bom_name'] ?? ''));

        if ($outputItemCode === '' || $bomName === '') {
            $this->command?->warn('Skipped BOM because output_item_code or bom_name is missing.');

            return;
        }

        $outputItem = $this->itemByCode($company, $outputItemCode);

        if (! $outputItem) {
            $this->command?->warn("Skipped BOM {$bomName}; output item {$outputItemCode} is not in ItemSeeder data.");

            return;
        }

        $componentItems = $this->componentItems($company, $bomRows);

        if ($componentItems === null) {
            $this->command?->warn("Skipped BOM {$bomName}; one or more component items are not in ItemSeeder data.");

            return;
        }

        $bomHeader = $this->bomHeaderFor($company, $branch, $outputItem, $first);
        $bomHeader->branches()->syncWithoutDetaching([$branch->id]);

        BomLine::query()
            ->where('bom_header_id', $bomHeader->id)
            ->delete();

        foreach ($bomRows as $row) {
            $componentCode = trim((string) ($row['component_item_code'] ?? ''));
            $componentItem = $componentItems->get($componentCode);

            if (! $componentItem) {
                continue;
            }

            BomLine::query()->create([
                'bom_header_id' => $bomHeader->id,
                'component_item_id' => $componentItem->id,
                'unit_id' => $componentItem->unit_id,
                'quantity' => $this->decimal($row['quantity'] ?? 1, 1),
                'wastage_percent' => $this->decimal($row['wastage_percent'] ?? 0, 0),
                'estimated_cost' => $this->decimal($row['estimated_cost'] ?? 0, 0),
                'note' => $this->nullableString($row['note'] ?? null),
            ]);
        }

        $this->linkMenuToBom($company, $branch, (string) ($first['menu_name'] ?? ''), $outputItem, $bomHeader);
        $this->command?->info("Seeded BOM: {$bomHeader->bom_no} - {$bomHeader->name}");
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
                $headers = array_map(fn ($header): string => Str::of((string) $header)
                    ->replace("\xEF\xBB\xBF", '')
                    ->trim()
                    ->lower()
                    ->replace(' ', '_')
                    ->toString(), $row);

                continue;
            }

            $record = array_combine(
                $headers,
                array_map(fn ($value): string => trim((string) $value), array_pad($row, count($headers), null))
            );

            if (! $record || (($record['bom_name'] ?? '') === '' && ($record['output_item_code'] ?? '') === '')) {
                continue;
            }

            $rows[] = $record;
        }

        return $rows;
    }

    private function branchFor(string $branchCode): ?Branch
    {
        $branchCode = trim($branchCode);
        $query = Branch::query()->with('company')->orderBy('id');

        if (preg_match('/^B(\d+)$/i', $branchCode, $matches)) {
            return Branch::query()
                ->with('company')
                ->orderBy('id')
                ->skip(max(0, ((int) $matches[1]) - 1))
                ->first();
        }

        return $branchCode === ''
            ? $query->first()
            : $query->where('code', $branchCode)->first();
    }

    private function itemByCode(Company $company, string $code): ?Item
    {
        return Item::query()
            ->where('company_id', $company->id)
            ->whereNull('branch_id')
            ->where('code', trim($code))
            ->where('is_active', true)
            ->first();
    }

    /**
     * @param  Collection<int, array<string, string>>  $bomRows
     * @return Collection<string, Item>|null
     */
    private function componentItems(Company $company, Collection $bomRows): ?Collection
    {
        $codes = $bomRows
            ->pluck('component_item_code')
            ->map(fn ($code): string => trim((string) $code))
            ->filter()
            ->unique()
            ->values();

        $items = Item::query()
            ->where('company_id', $company->id)
            ->whereNull('branch_id')
            ->where('is_active', true)
            ->whereIn('code', $codes)
            ->get()
            ->keyBy('code');

        return $items->count() === $codes->count() ? $items : null;
    }

    private function bomHeaderFor(Company $company, Branch $branch, Item $outputItem, array $row): BomHeader
    {
        $bomNo = trim((string) ($row['bom_no'] ?? ''));
        $bomName = trim((string) ($row['bom_name'] ?? ''));
        $status = $this->validStatus((string) ($row['status'] ?? 'active'));

        $data = [
            'company_id' => $company->id,
            'branch_id' => null,
            'output_item_id' => $outputItem->id,
            'name' => $bomName,
            'output_quantity' => $this->decimal($row['output_quantity'] ?? 1, 1),
            'status' => $status,
            'effective_from' => $this->dateOrNull($row['effective_from'] ?? null),
            'effective_to' => $this->dateOrNull($row['effective_to'] ?? null),
            'note' => $this->nullableString($row['note'] ?? null),
        ];

        if ($bomNo !== '') {
            return BomHeader::query()->updateOrCreate(['bom_no' => $bomNo], $data);
        }

        $existing = BomHeader::query()
            ->where('company_id', $company->id)
            ->where('output_item_id', $outputItem->id)
            ->where('name', $bomName)
            ->first();

        if ($existing) {
            $existing->fill($data)->save();

            return $existing;
        }

        return BomHeader::query()->create([
            ...$data,
            'bom_no' => DocumentNumber::make(BomHeader::class, 'bom_no', 'BM', $branch),
        ]);
    }

    private function linkMenuToBom(Company $company, Branch $branch, string $menuName, Item $outputItem, BomHeader $bomHeader): void
    {
        $menuName = trim($menuName);

        $menu = Menu::query()
            ->where('company_id', $company->id)
            ->whereHas('branches', fn ($query) => $query->whereKey($branch->id))
            ->where(function ($query) use ($menuName, $outputItem): void {
                $query->where('item_id', $outputItem->id);

                if ($menuName !== '') {
                    $query->orWhere('name', $menuName);
                }
            })
            ->first();

        $menu?->update([
            'item_id' => $outputItem->id,
            'bom_header_id' => $bomHeader->id,
        ]);
    }

    private function validStatus(string $status): string
    {
        $status = strtolower(trim($status));

        return in_array($status, ['draft', 'active', 'inactive'], true) ? $status : 'active';
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

        return $value === '' ? null : $value;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
