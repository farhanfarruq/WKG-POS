<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Users
            'manage_users', 'view_users',
            // Products & Categories
            'manage_products', 'view_products',
            // Inventory
            'manage_inventory', 'view_inventory',
            // Orders
            'manage_orders', 'view_orders', 'create_transaction', 'cancel_order',
            // Shift
            'manage_shifts', 'close_shift',
            // Reports
            'view_reports',
            // Purchase Orders
            'manage_purchase_orders',
        ];

        $guards = ['web', 'sanctum'];

        foreach ($guards as $guard) {
            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
            }

            $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => $guard]);
            $admin      = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
            $manager    = Role::firstOrCreate(['name' => 'manager', 'guard_name' => $guard]);
            $kasir      = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => $guard]);
            $barista    = Role::firstOrCreate(['name' => 'barista', 'guard_name' => $guard]);

            // Super Admin - all permissions
            $superAdmin->syncPermissions(Permission::where('guard_name', $guard)->get());

            // Admin
            $admin->syncPermissions([
                'manage_users', 'view_users',
                'manage_products', 'view_products',
                'manage_inventory', 'view_inventory',
                'manage_orders', 'view_orders', 'create_transaction', 'cancel_order',
                'manage_shifts', 'close_shift',
                'view_reports',
                'manage_purchase_orders',
            ]);

            // Manager
            $manager->syncPermissions([
                'view_users',
                'manage_products', 'view_products',
                'manage_inventory', 'view_inventory',
                'manage_orders', 'view_orders', 'create_transaction', 'cancel_order',
                'close_shift', 'manage_shifts',
                'view_reports',
            ]);

            // Kasir
            $kasir->syncPermissions([
                'view_products',
                'view_orders', 'create_transaction',
                'manage_shifts', 'close_shift',
            ]);

            // Barista (KDS access only)
            $barista->syncPermissions([
                'view_orders',
            ]);
        }

        $this->command->info('Roles & Permissions seeded successfully!');
    }
}
