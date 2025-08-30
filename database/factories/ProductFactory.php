<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'name' => fake()->randomElement(['Nasi Goreng', 'Kopi Gayo', 'Americano']),
            'price' => fake()->randomElement([15000.00, 26000.00, 35000.00]),
            'description' => fake()->sentence(),
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'featured' => fake()->randomElement([true, false])
        ];
    }
}
