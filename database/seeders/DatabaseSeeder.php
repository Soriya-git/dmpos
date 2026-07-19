<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            RolePermissionSeeder::class,
            CompanySeeder::class,
            CustomerSeeder::class,
            SupplierSeeder::class,
            DiningResourceSeeder::class,
            TaxSeeder::class,
            ExchangeRateSeeder::class,
            BanknoteSeeder::class,
            PaymentMethodSeeder::class,
            UnitSeeder::class,
            ItemSeeder::class,
            PrinterSeeder::class,
            MenuSeeder::class,
            MenuPriceListSeeder::class,
            BomSeeder::class,
            WarehouseSeeder::class,
            AuditLogSeeder::class,
        ]);
    }
}
