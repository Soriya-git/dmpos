<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\PosTerminal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Demo Restaurant Group',
                'code' => 'DRG',
                'branches' => [
                    ['name' => 'DRG Riverside', 'code' => 'DM01'],
                    ['name' => 'DRG Central', 'code' => 'DM02'],
                ],
                'users' => [
                    ['name' => 'DRG Admin', 'email' => 'drg.admin@diamond.com', 'role' => 'Admin'],
                    ['name' => 'DRG Manager', 'email' => 'drg.manager@diamond.com', 'role' => 'Manager'],
                    ['name' => 'DRG Cashier', 'email' => 'drg.cashier@diamond.com', 'role' => 'Supervisor'],
                    ['name' => 'DRG Server', 'email' => 'drg.server@diamond.com', 'role' => 'Staff'],
                ],
            ],
            [
                'name' => 'Metro Cafe Group',
                'code' => 'MCG',
                'branches' => [
                    ['name' => 'MCG Downtown', 'code' => 'MCG-DTN'],
                    ['name' => 'MCG Airport', 'code' => 'MCG-AIR'],
                ],
                'users' => [
                    ['name' => 'MCG Admin', 'email' => 'mcg.admin@diamond.com', 'role' => 'Admin'],
                    ['name' => 'MCG Manager', 'email' => 'mcg.manager@diamond.com', 'role' => 'Manager'],
                    ['name' => 'MCG Cashier', 'email' => 'mcg.cashier@diamond.com', 'role' => 'Supervisor'],
                    ['name' => 'MCG Server', 'email' => 'mcg.server@diamond.com', 'role' => 'Staff'],
                ],
            ],
        ];

        foreach ($companies as $companyData) {
            $company = Company::create([
                'name' => $companyData['name'],
                'code' => $companyData['code'],
                'email' => strtolower($companyData['code']).'@diamond.com',
                'phone' => '010000'.str_pad((string) Company::count(), 4, '0', STR_PAD_LEFT),
                'address' => $companyData['name'].' Head Office',
            ]);

            $branches = collect($companyData['branches'])->map(function (array $branchData, int $branchIndex) use ($company) {
                $branch = Branch::create([
                    'company_id' => $company->id,
                    'name' => $branchData['name'],
                    'code' => $branchData['code'],
                    'phone' => '011000'.str_pad((string) ($company->id * 10 + $branchIndex + 1), 4, '0', STR_PAD_LEFT),
                    'vat_number' => 'VAT'.str_pad((string) ($company->id * 100 + $branchIndex + 1), 7, '0', STR_PAD_LEFT),
                    'address' => $branchData['name'].' Address',
                ]);

                foreach (range(1, 2) as $terminalNumber) {
                    PosTerminal::create([
                        'company_id' => $company->id,
                        'branch_id' => $branch->id,
                        'name' => 'POS-'.$branchData['code'].'-'.str_pad((string) $terminalNumber, 2, '0', STR_PAD_LEFT),
                        'code' => $branchData['code'].'-POS'.str_pad((string) $terminalNumber, 2, '0', STR_PAD_LEFT),
                        'device_type' => $terminalNumber === 1 ? 'desktop' : 'tablet',
                    ]);
                }

                Customer::create([
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'name' => 'General Customer',
                    'phone_number' => '000000'.$company->id.$branch->id,
                    'is_general_customer' => true,
                ]);

                return $branch;
            });

            foreach ($companyData['users'] as $userIndex => $userData) {
                $user = User::create([
                    'company_id' => $company->id,
                    'branch_id' => $branches[$userIndex % $branches->count()]->id,
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]);

                $user->branches()->sync($branches->pluck('id')->all());
                $user->assignRole(Role::findOrCreate($userData['role'], 'web'));
            }
        }
    }
}
