<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            return;
        }

        ExchangeRate::create([
            'company_id' => $company->id,
            'from_currency' => 'USD',
            'to_currency' => 'KHR',
            'rate' => 4100,
            'effective_date' => now()->toDateString(),
            'is_active' => true,
        ]);
    }
}