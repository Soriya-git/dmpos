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
            CompanySeeder::class,
            CustomerSeeder::class,
            SupplierSeeder::class,
            DiningResourceSeeder::class,
            TaxSeeder::class,
            ExchangeRateSeeder::class,
            PaymentMethodSeeder::class,
            UnitSeeder::class,
            MenuSeeder::class,
            BomSeeder::class,
            WarehouseSeeder::class,
            PurchaseSeeder::class,
            StockOperationSeeder::class,
            PrinterSeeder::class,
            OrderKitchenSeeder::class,
            InvoicePaymentSeeder::class,
            AuditLogSeeder::class,
        ]);
    }
}
