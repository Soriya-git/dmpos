<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        collect([
            'System Admin',
            'Admin',
            'Manager',
            'Supervisor',
            'Staff',
        ])->each(fn (string $name) => Role::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));
    }
}
