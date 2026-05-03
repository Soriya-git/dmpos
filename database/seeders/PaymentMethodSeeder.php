<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $branch = Branch::first();

        if (!$company || !$branch) {
            return;
        }

        $methods = [
            ['Cash USD', 'CASH_USD', 'cash', 'USD', true],
            ['Cash KHR', 'CASH_KHR', 'cash', 'KHR', false],
            ['E-Banking USD', 'EBANK_USD', 'bank', 'USD', false],
            ['E-Banking KHR', 'EBANK_KHR', 'bank', 'KHR', false],
        ];

        foreach ($methods as [$name, $code, $type, $currency, $isDefault]) {
            PaymentMethod::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => $name,
                'code' => $code,
                'method_type' => $type,
                'currency' => $currency,
                'is_default' => $isDefault,
                'is_active' => true,
            ]);
        }
    }
}