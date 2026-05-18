<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->with('branches')->get()->each(function (Company $company): void {
            $branch = $company->branches->first();

            foreach ([
                [
                    'code' => 'SUP-DEMO',
                    'name' => 'Demo Supplier',
                    'contact_person' => 'Operations Desk',
                    'phone_number' => '012345678',
                    'email' => 'demo.supplier'.$company->id.'@diamond.com',
                    'address' => 'Primary demo supplier account',
                    'note' => 'Seed supplier used by purchase order demo data.',
                ],
                [
                    'code' => 'SUP-FRESH',
                    'name' => 'Fresh Market Produce',
                    'contact_person' => 'Market Sales',
                    'phone_number' => '089200'.$company->id.'001',
                    'email' => 'fresh'.$company->id.'@supplier.example',
                    'address' => 'Fresh ingredients wholesale account',
                    'note' => 'Seed supplier for produce and daily ingredients.',
                ],
                [
                    'code' => 'SUP-PACK',
                    'name' => 'Kitchen Packaging Co.',
                    'contact_person' => 'Account Service',
                    'phone_number' => '089200'.$company->id.'002',
                    'email' => 'packaging'.$company->id.'@supplier.example',
                    'address' => 'Packaging and disposables account',
                    'note' => 'Seed supplier for packaging and restaurant supplies.',
                ],
            ] as $supplierData) {
                Supplier::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'code' => $supplierData['code'],
                    ],
                    [
                        ...$supplierData,
                        'company_id' => $company->id,
                        'branch_id' => $branch?->id,
                        'is_active' => true,
                    ],
                );
            }
        });
    }
}
