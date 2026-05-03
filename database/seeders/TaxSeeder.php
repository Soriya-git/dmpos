<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            return;
        }

        Tax::create([
            'company_id' => $company->id,
            'name' => 'No Tax',
            'code' => 'NO_TAX',
            'rate' => 0,
            'is_default' => true,
        ]);

        Tax::create([
            'company_id' => $company->id,
            'name' => 'VAT 10%',
            'code' => 'VAT_10',
            'rate' => 10,
            'is_default' => false,
        ]);
    }
}