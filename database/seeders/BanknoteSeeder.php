<?php

namespace Database\Seeders;

use App\Models\Banknote;
use Illuminate\Database\Seeder;

class BanknoteSeeder extends Seeder
{
    public function run(): void
    {
        $banknotes = [
            'KHR' => [100, 500, 1000, 5000, 10000, 20000, 50000, 100000],
            'USD' => [1, 2, 5, 10, 20, 100],
        ];

        foreach ($banknotes as $currency => $denominations) {
            foreach ($denominations as $index => $denomination) {
                Banknote::updateOrCreate(
                    [
                        'currency_type' => $currency,
                        'denomination' => $denomination,
                    ],
                    [
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
