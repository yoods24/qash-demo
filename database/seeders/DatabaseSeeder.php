<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Career;
use App\Models\Product;
use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Sequence;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1) Create the central Qash admin first
        $this->call(QashAdminSeeder::class);

        // 2) Create a demo tenant (single shared DB)
        $tenant = Tenant::firstOrCreate([
            'id' => 'demo-cafe',
        ], [
            'data' => [
                'name' => 'Demo Cafe',
                'description' => 'Seeded demo tenant',
            ],
        ]);

        // 3) Seed default shifts for the tenant
        $this->call(ShiftSeeder::class);
        $fixedShift = \App\Models\Shift::where('tenant_id', $tenant->id)->where('name', 'Fixed 9-6')->first();

        // 4) Create a primary admin within the tenant
        $admin = User::firstOrCreate([
            'email' => 'admin@demo-cafe.test',
        ], [
            'firstName' => 'Demo',
            'lastName' => 'Admin',
            'password' => Hash::make('Miscrits24!'),
            'tenant_id' => $tenant->id,
            'status' => 1,
            'is_admin' => true,
            'shift_id' => $fixedShift->id,
        ]);

        // 5) Seed base permissions/roles & assign within the tenant context
        if (function_exists('tenancy')) {
            tenancy()->initialize($tenant);
        }

        $this->call(PermissionRoleSeeder::class);
        $this->call(TenantNavigationPermissionsSeeder::class);

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $admin->assignRole($superAdminRole);
        $admin->syncPermissions(Permission::all());

        if (function_exists('tenancy')) {
            tenancy()->end();
        }

        // 6) Seed tenant-scoped data (categories, products, careers)
        $categories = Category::factory()
            ->count(3)
            ->state(new Sequence(
                ['name' => 'Non Beverage'],
                ['name' => 'Coffee'],
                ['name' => 'Non Coffee']
            ))
            ->create(['tenant_id' => $tenant->id]);

        // Create sample products under this tenant
        Product::factory()
            ->count(3)
            ->state(new Sequence(
                ['name' => 'Kopi Gayo'],
                ['name' => 'Americano'],
                ['name' => 'Sushi']
            ))
            ->create([
                'tenant_id' => $tenant->id,
                // Let factory choose a random category; all categories above belong to this tenant
            ]);

        // Careers for the tenant
        Career::factory(10)->create([
            'tenant_id' => $tenant->id,
        ]);

        // 7) Add a couple of demo staff users for testing
        $this->call(DemoCafeUserSeeder::class);
    }
}
