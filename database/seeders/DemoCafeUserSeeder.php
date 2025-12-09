<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCafeUserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(['id' => 'demo-cafe'], [
            'data' => [
                'name' => 'Demo Cafe',
                'description' => 'Seeded demo tenant',
            ],
        ]);

        $fixed = Shift::firstOrCreate([
            'tenant_id' => $tenant->id,
            'name' => 'Fixed 9-6',
        ], [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'week_off_days' => [7],
            'recurring' => true,
            'status' => 'active',
        ]);

        $evening = Shift::firstOrCreate([
            'tenant_id' => $tenant->id,
            'name' => 'Evening 2-11',
        ], [
            'start_time' => '14:00',
            'end_time' => '23:00',
            'week_off_days' => [1],
            'recurring' => true,
            'status' => 'active',
        ]);

        // Seed demo staff with role assignments
        if (function_exists('tenancy')) {
            tenancy()->initialize($tenant);
        }

        $users = [
            [
                'email' => 'barista@demo-cafe.test',
                'firstName' => 'Barista',
                'lastName' => 'One',
                'gender' => 'Male',
                'blood_group' => 'O',
                'shift_id' => $fixed->id,
                'role' => 'Kitchen',
            ],
            [
                'email' => 'cashier@demo-cafe.test',
                'firstName' => 'Cashier',
                'lastName' => 'Two',
                'gender' => 'Female',
                'blood_group' => 'A',
                'shift_id' => $evening->id,
                'role' => 'Cashier',
            ],
            [
                'email' => 'waiter@demo-cafe.test',
                'firstName' => 'Waiter',
                'lastName' => 'Three',
                'gender' => 'Male',
                'blood_group' => 'B',
                'shift_id' => $fixed->id,
                'role' => 'Waiter',
            ],
            [
                'email' => 'sales@demo-cafe.test',
                'firstName' => 'Sales',
                'lastName' => 'Four',
                'gender' => 'Female',
                'blood_group' => 'AB',
                'shift_id' => $evening->id,
                'role' => 'Sales',
            ],
            [
                'email' => 'hr@demo-cafe.test',
                'firstName' => 'HR',
                'lastName' => 'Five',
                'gender' => 'Female',
                'blood_group' => 'O',
                'shift_id' => $fixed->id,
                'role' => 'HR',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'firstName' => $userData['firstName'],
                    'lastName' => $userData['lastName'],
                    'password' => Hash::make('Miscrits24!'),
                    'tenant_id' => $tenant->id,
                    'status' => 1,
                    'is_admin' => false,
                    'gender' => $userData['gender'],
                    'blood_group' => $userData['blood_group'],
                    'shift_id' => $userData['shift_id'],
                ]
            );

            $role = Role::firstOrCreate(['name' => $userData['role']]);
            $user->assignRole($role);
        }

        if (function_exists('tenancy')) {
            tenancy()->end();
        }
    }
}
