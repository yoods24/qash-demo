<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Permission;
use App\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the admin user
        $user = User::create([
            'firstName' => 'Admin',
            'lastName' => 'User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('Miscrits24!'),
            'is_admin' => 1,
        ]);

        // Get or create the 'Super Admin' role (if you want a specific role for the super admin)
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);

        // Assign the 'Super Admin' role to the user
        $user->assignRole($superAdminRole);

        // Give the user all permissions
        $permissions = Permission::all(); // Get all permissions from the database
        $user->syncPermissions($permissions); // Assign all permissions to the superadmin

        // Optionally, if you have permissions defined in a seeder or manually,
        // you can assign specific permissions like so:
        // $user->givePermissionTo('create-product', 'edit-product', 'delete-product');
    }
}
