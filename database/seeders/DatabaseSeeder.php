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
        $this->call(TenantDefaultRolesSeeder::class);

        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $ownerRole->syncPermissions(Permission::all());
        $admin->assignRole($ownerRole);

        if (function_exists('tenancy')) {
            tenancy()->end();
        }

        // 6) Seed tenant-scoped data (categories, products, careers)
        // Create exactly 5 menu categories for the customer view
        $categories = Category::factory()
            ->count(5)
            ->state(new Sequence(
                ['name' => 'Breakfast'],
                ['name' => 'Dinner'],
                ['name' => 'Drinks'],
                ['name' => 'Healthy'],
                ['name' => 'Vegetarian'],
            ))
            ->create(['tenant_id' => $tenant->id]);

        // Create ~30 natural-looking products distributed across the 5 categories
        $namedProductsByCategory = [
            'Breakfast' => [
                'Shakshouka',
                'Avocado Toast',
                'Buttermilk Pancakes',
                'Granola Yogurt Parfait',
                'Brioche French Toast',
                'Breakfast Burrito',
            ],
            'Dinner' => [
                'Grilled Salmon with Lemon Butter',
                'Herb Roasted Chicken',
                'Beef Bolognese Pasta',
                'Mushroom Risotto',
                'BBQ Short Ribs',
                'Garlic Butter Prawn Linguine',
            ],
            'Drinks' => [
                'Iced Caramel Latte',
                'Cold Brew Coffee',
                'Fresh Orange Juice',
                'Sparkling Citrus Lemonade',
                'House Iced Tea',
                'Mixed Berry Smoothie',
            ],
            'Healthy' => [
                'Quinoa Buddha Bowl',
                'Grilled Chicken Salad',
                'Roasted Veggie Bowl',
                'Salmon Poke Bowl',
                'Green Detox Salad',
                'Chickpea Power Bowl',
            ],
            'Vegetarian' => [
                'Margherita Flatbread',
                'Mushroom Stroganoff',
                'Roasted Veggie Lasagne',
                'Halloumi Grain Salad',
                'Pumpkin & Coconut Soup',
                'Falafel Wrap with Tahini',
            ],
        ];

        foreach ($categories as $category) {
            $names = $namedProductsByCategory[$category->name] ?? [];

            if (empty($names)) {
                continue;
            }

            Product::factory()
                ->count(count($names))
                ->state(new Sequence(...array_map(fn ($name) => ['name' => $name], $names)))
                ->create([
                    'tenant_id'   => $tenant->id,
                    'category_id' => $category->id,
                ]);
        }

        // Careers for the tenant
        Career::factory(10)->create([
            'tenant_id' => $tenant->id,
        ]);

        // Events
        $this->callWith(EventSeeder::class, ['tenantId' => $tenant->id]);

        // 7) Add a couple of demo staff users for testing
        $this->call(DemoCafeUserSeeder::class);
    }
}
