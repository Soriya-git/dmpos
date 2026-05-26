<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __invoke(Request $request)
    {
        $companyId = $request->user()->company_id;

        $users = User::query()
            ->with(['branch:id,name', 'branches:id,name', 'roles.permissions', 'permissions'])
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get()
            ->map(function (User $user): array {
                $permissions = $user->effectivePermissionNames();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'image' => $user->image,
                    'image_url' => $user->image_url,
                    'created_at' => $user->created_at?->toISOString(),
                    'updated_at' => $user->updated_at?->toISOString(),
                    'email_verified_at' => $user->email_verified_at?->toISOString(),
                    'role' => $user->roles->pluck('name')->first() ?? 'No Role',
                    'roles' => $user->roles->pluck('name')->values(),
                    'branch' => $user->branch?->name ?? 'Unassigned',
                    'branches' => $user->branches->pluck('name')->values(),
                    'status' => $user->email_verified_at ? 'active' : 'pending',
                    'permissions' => $permissions,
                    'permissions_by_action' => [
                        'view' => $permissions->contains(fn (string $permission): bool => str_ends_with($permission, '.view')),
                        'create' => $permissions->contains(fn (string $permission): bool => str_ends_with($permission, '.create')),
                        'update' => $permissions->contains(fn (string $permission): bool => str_ends_with($permission, '.update')),
                        'delete' => $permissions->contains(fn (string $permission): bool => str_ends_with($permission, '.delete')),
                        'manage' => $permissions->contains(fn (string $permission): bool => str_contains($permission, '.manage')),
                    ],
                ];
            });

        $permissions = Permission::query()
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name'])
            ->map(fn (Permission $permission): array => [
                'id' => $permission->id,
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
                'module' => str($permission->name)->before('.')->headline()->toString(),
                'action' => str($permission->name)->after('.')->headline()->toString(),
            ]);

        $roles = Role::query()
            ->when(! $request->user()->hasRole('System Admin'), fn ($query) => $query->where('name', '!=', 'System Admin'))
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name'])
            ->map(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions_count' => $role->permissions->count(),
                'permissions' => $role->permissions->pluck('name')->values(),
            ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'branches' => Branch::query()
                ->where('company_id', $companyId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'summary' => [
                'users' => $users->count(),
                'roles' => $roles->count(),
                'permissions' => $permissions->count(),
            ],
        ]);
    }

    public function storeRole(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        abort_if(
            str($data['name'])->trim()->lower()->toString() === 'system admin' && ! $request->user()->hasRole('System Admin'),
            403,
        );

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions(Permission::query()
            ->whereIn('id', $data['permission_ids'] ?? [])
            ->get());

        return back()->with('success', 'Role created successfully.');
    }

    public function updatePermissions(Request $request, User $user)
    {
        abort_if(
            ! $request->user()->hasRole('System Admin') && (int) $user->company_id !== (int) $request->user()->company_id,
            403,
        );

        abort_if(
            $user->hasRole('System Admin') && ! $request->user()->hasRole('System Admin'),
            403,
        );

        $data = $request->validate([
            'permission_names' => ['array'],
            'permission_names.*' => ['string', 'exists:permissions,name'],
            'permission_scope' => ['array'],
            'permission_scope.*' => ['string', 'exists:permissions,name'],
        ]);

        $targetPermissionNames = collect($data['permission_names'] ?? [])->unique()->values();
        $permissionScope = collect($data['permission_scope'] ?? $data['permission_names'] ?? [])->unique()->values();
        $allPermissions = Permission::query()
            ->whereIn('name', $permissionScope)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->keyBy('name');
        $basePermissionNames = $user->getAllPermissions()->pluck('name');
        $syncPayload = [];

        foreach ($allPermissions as $permissionName => $permission) {
            $shouldAllow = $targetPermissionNames->contains($permissionName);
            $baseAllows = $basePermissionNames->contains($permissionName);

            if ($shouldAllow === $baseAllows) {
                continue;
            }

            $syncPayload[$permission->id] = [
                'allowed' => $shouldAllow,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $user->permissionOverrides()->detach($allPermissions->pluck('id')->all());

        if ($syncPayload !== []) {
            $user->permissionOverrides()->syncWithoutDetaching($syncPayload);
        }

        return back()->with('success', 'User permissions updated successfully.');
    }
}
