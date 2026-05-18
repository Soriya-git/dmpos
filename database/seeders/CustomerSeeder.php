<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = collect([
            ['name' => 'Retail', 'code' => 'GRP-RET', 'description' => 'Default walk-in and retail guests.'],
            ['name' => 'VIP', 'code' => 'GRP-VIP', 'description' => 'Preferred guests with elevated service handling.'],
            ['name' => 'Corporate', 'code' => 'GRP-COR', 'description' => 'Company accounts and event catering customers.'],
        ])->mapWithKeys(function (array $groupData): array {
            $group = CustomerGroup::updateOrCreate(
                ['code' => $groupData['code']],
                $groupData,
            );

            return [$groupData['name'] => $group];
        });

        Company::query()->with('branches')->get()->each(function (Company $company) use ($groups): void {
            $branch = $company->branches->first();

            Customer::query()
                ->where('company_id', $company->id)
                ->where('is_general_customer', true)
                ->update(['customer_group_id' => $groups['Retail']->id]);

            foreach ([
                [
                    'name' => 'Diamond Catering Co.',
                    'phone_number' => '089100'.$company->id.'001',
                    'email' => 'catering'.$company->id.'@diamond.example',
                    'address' => 'Corporate events account',
                    'customer_group_id' => $groups['Corporate']->id,
                    'note' => 'Seed customer for catering and company billing workflows.',
                ],
                [
                    'name' => 'Sokha Family',
                    'phone_number' => '089100'.$company->id.'002',
                    'email' => 'sokha'.$company->id.'@diamond.com',
                    'address' => 'Family dining account',
                    'customer_group_id' => $groups['VIP']->id,
                    'note' => 'Seed customer for VIP dining workflows.',
                ],
            ] as $customerData) {
                Customer::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'phone_number' => $customerData['phone_number'],
                    ],
                    [
                        ...$customerData,
                        'company_id' => $company->id,
                        'branch_id' => $branch?->id,
                        'is_general_customer' => false,
                        'is_active' => true,
                    ],
                );
            }
        });
    }
}
