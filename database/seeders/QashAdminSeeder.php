<?php

namespace Database\Seeders;

use App\Models\QashUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class QashAdminSeeder extends Seeder
{
    public function run(): void
    {
        QashUser::firstOrCreate(
            ['email' => 'owner@qash.test'],
            [
                'name' => 'Yuda',
                'password' => Hash::make('password123'),
            ]
        );
    }
}

