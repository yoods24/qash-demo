<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class TenantNavigationPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        if (! function_exists('tenant') || ! tenant('id')) {
            throw new \RuntimeException('TenantNavigationPermissionsSeeder must be executed within a tenant context.');
        }

        $modules = config('tenant_permissions.modules', []);
        $names = [];

        foreach ($modules as $module) {
            $names[] = $module['view'];

            foreach ($module['children'] ?? [] as $child) {
                $names[] = $child['view'];

                foreach ($child['actions'] ?? [] as $action) {
                    $names[] = $action['name'];
                }
            }
        }

        $names = array_values(array_unique(array_filter($names)));

        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // Keep Super Admin role in sync with every permission for the tenant
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole->syncPermissions(Permission::all());
    }
}
