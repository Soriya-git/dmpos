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
        $company = Company::first();
        $branch = Branch::first();

        if (!$company || !$branch) {
            return;
        }

        $vipRoom = DiningResourceType::create([
            'company_id' => $company->id,
            'name' => 'VIP Room',
            'code' => 'VIP_ROOM',
        ]);

        $standardTable = DiningResourceType::create([
            'company_id' => $company->id,
            'name' => 'Standard Table',
            'code' => 'STD_TABLE',
        ]);

        $outdoorTable = DiningResourceType::create([
            'company_id' => $company->id,
            'name' => 'Outdoor Table',
            'code' => 'OUT_TABLE',
        ]);

        DiningResource::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'dining_resource_type_id' => $vipRoom->id,
            'name' => 'VIP Room 01',
            'code' => 'VIP-01',
            'capacity' => 10,
        ]);

        DiningResource::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'dining_resource_type_id' => $vipRoom->id,
            'name' => 'VIP Room 02',
            'code' => 'VIP-02',
            'capacity' => 8,
        ]);

        DiningResource::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'dining_resource_type_id' => $standardTable->id,
            'name' => 'Table A1',
            'code' => 'A1',
            'capacity' => 4,
        ]);

        DiningResource::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'dining_resource_type_id' => $standardTable->id,
            'name' => 'Table A2',
            'code' => 'A2',
            'capacity' => 4,
        ]);

        DiningResource::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'dining_resource_type_id' => $outdoorTable->id,
            'name' => 'Outdoor Table 01',
            'code' => 'OUT-01',
            'capacity' => 6,
        ]);
    }
}