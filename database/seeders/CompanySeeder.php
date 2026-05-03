<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Company;
use App\Models\Branch;
use App\Models\PosTerminal;
use App\Models\Customer;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::create([
            'name' => 'Demo Restaurant Group',
            'code' => 'DRG',
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Main Branch',
            'code' => 'MB01',
        ]);

        PosTerminal::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'POS-01',
            'device_type' => 'tablet',
        ]);

        // General Walk-in Customer
        Customer::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'General Customer',
            'phone_number' => '0000000000',
            'is_general_customer' => true,
        ]);
    }
}
