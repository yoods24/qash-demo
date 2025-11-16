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
        $min = $this->faker->numberBetween(2_500_000, 4_000_000);
        $max = $min + $this->faker->numberBetween(250_000, 2_000_000);

        return [
            'title' => $this->faker->jobTitle(),
            'salary_min' => $min,
            'salary_max' => $max,
            'about' => $this->faker->sentence(18),
            'responsibilities' => implode("\n", $this->faker->sentences(4)),
            'requirements' => implode("\n", $this->faker->sentences(4)),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => now(),
            'status' => $this->faker->boolean(80),
        ];
    }
}
