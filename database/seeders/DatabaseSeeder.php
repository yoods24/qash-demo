<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Career;
use App\Models\Product;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Career::factory(10)->create();
        User::factory(5)->create();
        
        Category::factory()
        ->count(3)
        ->state(new Sequence(
['name' => 'Non Beverage'], 
        ['name' => 'Coffee'],
        ['name' => 'Non Coffee'])
    )->create();
        

        Product::factory(3)->state(new Sequence(
            ['name' => 'Kopi Gayo',], 
        ['name' => 'Americano'],
        ['name' => 'Sushi'])
        )->create();

       $this->call([
        PermissionRoleSeeder::class,
        AdminSeeder::class
       ]);
    }
}
