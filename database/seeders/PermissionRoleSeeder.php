<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we are in a tenant context (single shared DB)
        if (! function_exists('tenant') || ! tenant('id')) {
            throw new \RuntimeException('PermissionRoleSeeder must be executed within a tenant context.');
        }

        $permissions = [
            'career_edit', 'career_delete', 'career_create',

            'product_','product_create', 'product_delete', ''
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $role = Role::firstOrCreate(['name' => 'Cashier']);
        $role->syncPermissions(['career_create', 'career_delete']);
    }
}
