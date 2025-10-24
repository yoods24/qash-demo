<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

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

        // Create a couple of sample staff under demo tenant
        User::firstOrCreate([
            'email' => 'barista@demo-cafe.test',
        ], [
            'firstName' => 'Barista',
            'lastName' => 'One',
            'password' => 'Miscrits24!',
            'tenant_id' => $tenant->id,
            'status' => 1,
            'is_admin' => false,
            'gender' => 'Male',
            'blood_group' => 'O',
            'shift_id' => $fixed->id,
        ]);

        User::firstOrCreate([
            'email' => 'cashier@demo-cafe.test',
        ], [
            'firstName' => 'Cashier',
            'lastName' => 'Two',
            'password' => 'Miscrits24!',
            'tenant_id' => $tenant->id,
            'status' => 1,
            'is_admin' => false,
            'gender' => 'Female',
            'blood_group' => 'A',
            'shift_id' => $evening->id,
        ]);
    }
}

