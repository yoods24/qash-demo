<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Career>
 */
class CareerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'salary' => fake()->randomElement([2300000,  4000000, 5200000]),
            'about' => fake()->text(), 
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'updated_at' => now(), // or fake()->dateTimeBetween($created_at, 'now') for realism
            'status' => fake()->boolean()
        ];
    }
}
