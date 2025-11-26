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
        $guard = Permission::getDefaultGuardName();
        $tenantId = tenant('id');

        // Collect all permission names from modules/children/actions
        $names = collect($modules)
            ->flatMap(function ($module) {
                $names = [$module['view'] ?? null];
                foreach ($module['children'] ?? [] as $child) {
                    $names[] = $child['view'] ?? null;
                    foreach ($child['actions'] ?? [] as $action) {
                        $names[] = $action['name'] ?? null;
                    }
                }
                return $names;
            })
            ->filter()
            ->unique()
            ->values();

        $namesArray = $names->all();

        // Avoid N x firstOrCreate calls by inserting only the missing names
        $existing = Permission::whereIn('name', $namesArray)->pluck('name')->all();
        $missing = array_values(array_diff($namesArray, $existing));

        if ($missing) {
            $now = now();
            $payload = array_map(function ($name) use ($guard, $tenantId, $now) {
                return [
                    'name' => $name,
                    'guard_name' => $guard,
                    'tenant_id' => $tenantId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $missing);

            foreach (array_chunk($payload, 100) as $chunk) {
                Permission::query()->insertOrIgnore($chunk);
            }
        }

        // Keep Super Admin role in sync with every permission for the tenant
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole->permissions()->sync(Permission::pluck('id')->all());
    }
}
