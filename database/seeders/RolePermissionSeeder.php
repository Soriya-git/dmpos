<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $actions = ['view', 'create', 'update', 'delete', 'manage'];
        $supervisorPages = [
            'dashboard',
            'orders',
            'daily-session-stock',
            'daily-session-menu',
            'sales',
            'purchase',
            'goods-receipts',
            'putaway',
            'balance-on-hand',
            'stock-settlements',
            'stock-adjustments',
            'internal-transfer',
            'stock-customer',
            'stock-write-off',
            'company-branches',
            'suppliers',
            'customers',
            'membership-cards',
            'products',
            'menu',
            'warehouse-locations',
            'seats',
            'exchange-rates',
            'taxes',
            'menu-price-lists',
            'printers',
            'printer-logs',
        ];

        $allPermissions = collect($supervisorPages)
            ->flatMap(fn (string $page) => collect($actions)->map(fn (string $action) => "{$page}.{$action}"))
            ->merge(collect($actions)->map(fn (string $action) => "pos-terminals.{$action}"))
            ->merge(['pos-sessions.open', 'pos-sessions.close'])
            ->unique()
            ->values();

        $allPermissions->each(fn (string $name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));

        $supervisorPermissions = collect($supervisorPages)
            ->flatMap(fn (string $page) => collect($actions)->map(fn (string $action) => "{$page}.{$action}"))
            ->merge(['pos-terminals.view', 'pos-sessions.open', 'pos-sessions.close'])
            ->values();

        Role::findOrCreate('System Admin', 'web')->syncPermissions($allPermissions->all());

        collect(['Admin', 'Manager', 'Supervisor'])->each(function (string $roleName) use ($supervisorPermissions): void {
            Role::findOrCreate($roleName, 'web')->syncPermissions($supervisorPermissions->all());
        });

        Role::findOrCreate('Staff', 'web')->syncPermissions([
            'pos-terminals.view',
            'pos-sessions.open',
            'pos-sessions.close',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
