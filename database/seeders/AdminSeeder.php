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

        // Get or create the Owner role (full access)
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);

        // Ensure the Owner role always contains every permission
        $ownerRole->syncPermissions(Permission::all());

        // Assign the Owner role to the user (no direct permissions needed)
        $user->assignRole($ownerRole);

        // Optionally, if you have permissions defined in a seeder or manually,
        // you can assign specific permissions like so:
        // $user->givePermissionTo('create-product', 'edit-product', 'delete-product');
    }
}
