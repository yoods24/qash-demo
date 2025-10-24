<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 'demo-cafe';

        Shift::firstOrCreate([
            'tenant_id' => $tenantId,
            'name' => 'Fixed 9-6',
        ], [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'week_off_days' => [7],
            'recurring' => true,
            'status' => 'active',
            'description' => 'Standard office shift',
        ]);

        Shift::firstOrCreate([
            'tenant_id' => $tenantId,
            'name' => 'Rotating 6-3',
        ], [
            'start_time' => '06:00',
            'end_time' => '15:00',
            'week_off_days' => [6,7],
            'recurring' => true,
            'status' => 'active',
        ]);

        // New: Evening shift for service team
        Shift::firstOrCreate([
            'tenant_id' => $tenantId,
            'name' => 'Evening 2-11',
        ], [
            'start_time' => '14:00',
            'end_time' => '23:00',
            'week_off_days' => [1], // Monday off by default
            'recurring' => true,
            'status' => 'active',
            'description' => 'Evening operations shift',
        ]);
    }
}
