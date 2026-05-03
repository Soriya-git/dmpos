<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\DiningResource;
use App\Models\DiningResourceType;
use Illuminate\Database\Seeder;

class DiningResourceSeeder extends Seeder
{
    public function run(): void
    {
        if (! Company::exists() || ! Branch::exists()) {
            return;
        }

        Company::with('branches')->get()->each(function (Company $company) {
            $types = collect([
                ['name' => 'Standard Table', 'code' => 'STD_TABLE'],
                ['name' => 'VIP Room', 'code' => 'VIP_ROOM'],
                ['name' => 'Outdoor Table', 'code' => 'OUT_TABLE'],
            ])->mapWithKeys(function (array $type) use ($company) {
                $resourceType = DiningResourceType::create([
                    'company_id' => $company->id,
                    'name' => $type['name'],
                    'code' => $company->code.'_'.$type['code'],
                ]);

                return [$type['code'] => $resourceType];
            });

            $company->branches->each(function (Branch $branch) use ($types) {
                $resources = [
                    ['type' => 'STD_TABLE', 'name' => 'Table 01', 'capacity' => 4],
                    ['type' => 'STD_TABLE', 'name' => 'Table 02', 'capacity' => 4],
                    ['type' => 'STD_TABLE', 'name' => 'Table 03', 'capacity' => 4],
                    ['type' => 'STD_TABLE', 'name' => 'Table 04', 'capacity' => 4],
                    ['type' => 'STD_TABLE', 'name' => 'Table 05', 'capacity' => 6],
                    ['type' => 'VIP_ROOM', 'name' => 'VIP Room 01', 'capacity' => 8],
                    ['type' => 'VIP_ROOM', 'name' => 'VIP Room 02', 'capacity' => 10],
                    ['type' => 'OUT_TABLE', 'name' => 'Outdoor Table 01', 'capacity' => 4],
                    ['type' => 'OUT_TABLE', 'name' => 'Outdoor Table 02', 'capacity' => 4],
                    ['type' => 'OUT_TABLE', 'name' => 'Outdoor Table 03', 'capacity' => 6],
                ];

                foreach ($resources as $index => $resource) {
                    DiningResource::create([
                        'company_id' => $branch->company_id,
                        'branch_id' => $branch->id,
                        'dining_resource_type_id' => $types[$resource['type']]->id,
                        'name' => $resource['name'],
                        'code' => $branch->code.'-DR'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                        'capacity' => $resource['capacity'],
                    ]);
                }
            });
        });
    }
}
