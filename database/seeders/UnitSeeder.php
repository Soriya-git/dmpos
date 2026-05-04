<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $pcs = Unit::updateOrCreate(
            ['code' => 'PCS'],
            [
                'name' => 'Piece',
                'category' => 'count',
                'unit_type' => 'reference',
                'base_unit_id' => null,
                'base_quantity' => 1,
                'description' => 'Reference unit for countable stock.',
                'is_active' => true,
            ]
        );

        $bottle = Unit::updateOrCreate(
            ['code' => 'BTL'],
            [
                'name' => 'Bottle',
                'category' => 'count',
                'unit_type' => 'reference',
                'base_unit_id' => null,
                'base_quantity' => 1,
                'description' => 'Reference unit for bottled items.',
                'is_active' => true,
            ]
        );

        $kg = Unit::updateOrCreate(
            ['code' => 'KG'],
            [
                'name' => 'Kilogram',
                'category' => 'weight',
                'unit_type' => 'reference',
                'base_unit_id' => null,
                'base_quantity' => 1,
                'description' => 'Reference unit for weight stock.',
                'is_active' => true,
            ]
        );

        $liter = Unit::updateOrCreate(
            ['code' => 'L'],
            [
                'name' => 'Liter',
                'category' => 'volume',
                'unit_type' => 'reference',
                'base_unit_id' => null,
                'base_quantity' => 1,
                'description' => 'Reference unit for volume stock.',
                'is_active' => true,
            ]
        );

        $units = [
            [
                'code' => 'G',
                'name' => 'Gram',
                'category' => 'weight',
                'unit_type' => 'smaller_than_reference',
                'base_unit_id' => $kg->id,
                'base_quantity' => 0.001,
                'description' => '1 gram = 0.001 kilogram.',
            ],
            [
                'code' => 'ML',
                'name' => 'Milliliter',
                'category' => 'volume',
                'unit_type' => 'smaller_than_reference',
                'base_unit_id' => $liter->id,
                'base_quantity' => 0.001,
                'description' => '1 milliliter = 0.001 liter.',
            ],
            [
                'code' => 'BOX',
                'name' => 'Box',
                'category' => 'package',
                'unit_type' => 'package',
                'base_unit_id' => $pcs->id,
                'base_quantity' => 12,
                'description' => 'Generic package example: 1 box = 12 pieces.',
            ],
            [
                'code' => 'TRAY',
                'name' => 'Tray',
                'category' => 'package',
                'unit_type' => 'package',
                'base_unit_id' => $pcs->id,
                'base_quantity' => 30,
                'description' => 'Generic tray example: 1 tray = 30 pieces.',
            ],
            [
                'code' => 'CASE12',
                'name' => 'Case (12 bottles)',
                'category' => 'package',
                'unit_type' => 'package',
                'base_unit_id' => $bottle->id,
                'base_quantity' => 12,
                'description' => '1 case = 12 bottles.',
            ],
            [
                'code' => 'PLT',
                'name' => 'Plate',
                'category' => 'service',
                'unit_type' => 'reference',
                'base_unit_id' => null,
                'base_quantity' => 1,
                'description' => 'Serving unit for menu output.',
            ],
            [
                'code' => 'CUP',
                'name' => 'Cup',
                'category' => 'service',
                'unit_type' => 'reference',
                'base_unit_id' => null,
                'base_quantity' => 1,
                'description' => 'Serving unit for drinks.',
            ],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['code' => $unit['code']],
                [
                    ...$unit,
                    'is_active' => true,
                ]
            );
        }
    }
}
