<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class TenantDefaultRolesSeeder extends Seeder
{
    public function run(): void
    {
        if (! function_exists('tenant') || ! tenant('id')) {
            throw new \RuntimeException('TenantDefaultRolesSeeder must be executed within a tenant context.');
        }

        $modules = config('tenant_permissions.modules', []);

        $roleModuleMap = [
            'Waiter' => ['pos', 'tables'],
            'Kitchen' => ['kitchen'],
            'HR' => ['hrm'],
            'Cashier' => ['pos', 'sales', 'reports'],
            'Sales' => ['sales', 'reports'],
        ];

        foreach ($roleModuleMap as $roleName => $moduleKeys) {
            $names = $this->collectPermissionNames($modules, $moduleKeys);

            if (empty($names)) {
                continue;
            }

            $role = Role::firstOrCreate(['name' => $roleName]);
            $permissions = Permission::whereIn('name', $names)->get();
            $role->syncPermissions($permissions);
        }
    }

    private function collectPermissionNames(array $modules, array $moduleKeys): array
    {
        $wanted = array_flip($moduleKeys);
        $names = [];

        foreach ($modules as $module) {
            if (! isset($wanted[$module['key'] ?? null])) {
                continue;
            }

            if (! empty($module['view'])) {
                $names[] = $module['view'];
            }

            foreach ($module['children'] ?? [] as $child) {
                if (! empty($child['view'])) {
                    $names[] = $child['view'];
                }
                foreach ($child['actions'] ?? [] as $action) {
                    if (! empty($action['name'])) {
                        $names[] = $action['name'];
                    }
                }
            }
        }

        return array_values(array_unique(array_filter($names)));
    }
}
