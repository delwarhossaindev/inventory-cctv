<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Create every permission from the catalog, tagged with its group.
        foreach (config('permissions.groups') as $group => $names) {
            foreach ($names as $name) {
                $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $permission->forceFill(['group' => $group])->save();
            }
        }

        // Super Admin (full access via Gate::before, still given every permission explicitly).
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Other default roles from the catalog.
        foreach (config('permissions.roles') as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }

        // Ensure the default admin user exists and is a Super Admin.
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Administrator', 'password' => Hash::make('password')]
        );
        $admin->syncRoles(['Super Admin']);

        // A demo salesperson to show role-limited access.
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@example.com'],
            ['name' => 'Cashier', 'password' => Hash::make('password')]
        );
        $cashier->syncRoles(['Salesperson']);
    }
}
