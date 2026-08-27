<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Permissions ---
        $permissions = [
            // Products
            'view products',
            'create products',
            'edit products',
            'delete products',

            // Inventory
            'view inventory',
            'adjust inventory',

            // Sales / POS
            'access pos',
            'view sales',
            'void sales',

            // Customers
            'view customers',
            'create customers',
            'edit customers',

            // Reports
            'view reports',

            // Users / Settings
            'manage users',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // --- Roles ---

        // Admin — full access
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // Cashier — POS counter only
        $cashier = Role::firstOrCreate(['name' => 'cashier']);
        $cashier->syncPermissions([
            'access pos',
            'view products',
            'view customers',
            'create customers',
        ]);

        // Stock Manager — inventory & products, no POS
        $stockManager = Role::firstOrCreate(['name' => 'stock_manager']);
        $stockManager->syncPermissions([
            'view products',
            'create products',
            'edit products',
            'view inventory',
            'adjust inventory',
            'view reports',
        ]);

        // --- Create a default admin user ---
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
        $adminUser->assignRole('admin');

        $this->command->info('Roles, permissions and default admin user created.');
        $this->command->info('Login: admin@admin.com / password');
    }
}
