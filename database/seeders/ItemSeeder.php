<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Branch;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('me_templates/item_master.csv');

        if (! is_file($path)) {
            return;
        }

        $items = $this->csvRows($path);

        Company::query()->orderBy('id')->each(function (Company $company) use ($items): void {
            $codes = collect($items)->pluck('code')->filter()->unique()->values();

            Item::query()
                ->where('company_id', $company->id)
                ->where(function ($query) use ($codes): void {
                    $query->whereNull('code')
                        ->orWhereNotIn('code', $codes->all());
                })
                ->update(['is_active' => false]);

            foreach ($items as $item) {
                $unit = $this->unitFor((string) $item['primary unit']);

                $masterItem = Item::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'branch_id' => null,
                        'code' => $item['code'],
                    ],
                    [
                        'unit_id' => $unit->id,
                        'name' => $item['name'],
                        'size' => $item['size'] ?: null,
                        'item_type' => $this->itemType((string) $item['type']),
                        'cost' => 0,
                        'minimum_stock_qty' => 0,
                        'is_stockable' => true,
                        'is_active' => true,
                        'description' => $item['description'] ?: null,
                    ]
                );

                $masterItem->branches()->sync(
                    Branch::query()
                        ->where('company_id', $company->id)
                        ->pluck('id')
                        ->mapWithKeys(fn ($branchId) => [$branchId => ['nickname' => null]])
                        ->all()
                );
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
                $headers = array_map(
                    fn ($header) => trim(str_replace("\xEF\xBB\xBF", '', (string) $header)),
                    $row
                );

                continue;
            }

            $values = array_pad($row, count($headers), null);
            $record = array_combine($headers, array_map(fn ($value) => trim((string) $value), $values));

            if (! $record || ($record['name'] ?? '') === '' || ($record['code'] ?? '') === '') {
                continue;
            }

            $rows[] = $record;
        }

        return $rows;
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
                'is_active' => true,
            ])->save();

            return $unit;
        }

        return Unit::create([
            'name' => $name,
            'code' => $code,
            'category' => in_array(strtolower($name), ['set', 'glass', 'piece'], true)
                ? 'count'
                : 'package',
            'unit_type' => 'reference',
            'base_unit_id' => null,
            'base_quantity' => 1,
            'description' => null,
            'is_active' => true,
        ]);
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

    private function itemType(string $type): string
    {
        $normalized = strtolower($type);

        if (str_contains($normalized, 'drink')
            || str_contains($normalized, 'beer')
            || str_contains($normalized, 'wine')
            || str_contains($normalized, 'whisky')
            || str_contains($normalized, 'cognac')
            || str_contains($normalized, 'champagne')
            || str_contains($normalized, 'tequila')
            || str_contains($normalized, 'spirit')
            || str_contains($normalized, 'water')
            || str_contains($normalized, 'stout')
            || str_contains($normalized, 'milk')) {
            return 'drink';
        }

        if (str_contains($normalized, 'fruit')) {
            return 'finished_product';
        }

        if (str_contains($normalized, 'consumable')) {
            return 'service_material';
        }

        return 'other';
    }
}
