<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            [
                'code' => 'SET',
                'name' => 'Set',
                'category' => 'count',
                'unit_type' => 'reference',
                'base_quantity' => 1,
            ],
            [
                'code' => 'GLASS',
                'name' => 'Glass',
                'category' => 'service',
                'unit_type' => 'reference',
                'base_quantity' => 1,
            ],
            [
                'code' => 'PACKS',
                'name' => 'Packs',
                'category' => 'package',
                'unit_type' => 'reference',
                'base_quantity' => 1,
            ],
            ['code' => 'CASE_24_BOTTLE', 'name' => 'Case of (24 Bottle)', 'base_quantity' => 24],
            ['code' => 'CASE_6_BOTTLE', 'name' => 'Case of (6 Bottle)', 'base_quantity' => 6],
            ['code' => 'CASE_10_PACKS', 'name' => 'Case of (10 Packs)', 'base_quantity' => 10],
            ['code' => 'CASE_3_BOTTLE', 'name' => 'Case of (3 Bottle)', 'base_quantity' => 3],
            ['code' => 'CASE_12_BOTTLE', 'name' => 'Case of (12 Bottle)', 'base_quantity' => 12],
            ['code' => 'CASE_20_PACK', 'name' => 'Case of (20 Pack)', 'base_quantity' => 20],
            ['code' => 'CASE_12_PIECE', 'name' => 'Case of (12 piece)', 'base_quantity' => 12],
            ['code' => 'CASE_24_CAN', 'name' => 'Case of (24 Can)', 'base_quantity' => 24],
            ['code' => 'CASE_24_PACK', 'name' => 'Case of (24 Pack)', 'base_quantity' => 24],
            ['code' => 'CASE_1_GLASS', 'name' => 'Case of (1 Glass)', 'base_quantity' => 1],
            ['code' => 'CASE_15_CAN', 'name' => 'Case of (15 Can)', 'base_quantity' => 15],
            ['code' => 'CASE_1_PACKS', 'name' => 'Case of (1 Packs)', 'base_quantity' => 1],
            ['code' => 'CASE_20_BOTTLE', 'name' => 'Case of (20 Bottle)', 'base_quantity' => 20],
            ['code' => 'CASE_30_BOTTLE', 'name' => 'Case of (30 Bottle)', 'base_quantity' => 30],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['code' => $unit['code']],
                [
                    'name' => $unit['name'],
                    'category' => $unit['category'] ?? 'package',
                    'unit_type' => $unit['unit_type'] ?? 'package',
                    'base_unit_id' => null,
                    'base_quantity' => $unit['base_quantity'],
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
