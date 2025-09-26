<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
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
        // Generate a sensible price and goods price (COGS <= price)
        $price = $this->faker->randomElement([15000.00, 22000.00, 26000.00, 35000.00, 42000.00]);
        $goodsPrice = round($price * $this->faker->randomFloat(2, 0.4, 0.85), 2);

        // Pick an existing product image from storage or fallback to a UI banner (always present in this repo)
        $imagePath = null;
        try {
            $files = Storage::disk('public')->files('products');
            if (!empty($files)) {
                $imagePath = $this->faker->randomElement($files);
            } else {
                // fallback to an existing asset under public/storage/ui
                $imagePath = 'ui/banner-coffee.png';
            }
        } catch (\Throwable $e) {
            $imagePath = 'ui/banner-coffee.png';
        }

        return [
            'price'        => $price,
            'goods_price'  => $goodsPrice,
            'description'  => $this->faker->sentence(),
            'category_id'  => Category::inRandomOrder()->value('id') ?? Category::factory(),
            'featured'     => $this->faker->boolean(30),
            'product_image'=> $imagePath,
            'alternate_name' => $this->faker->optional()->words(2, true),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            // Attach a default option (e.g., Size) with values
            $option = ProductOption::create([
                'product_id'  => $product->id,
                'name'        => 'Size',
                'is_required' => true,
                'tenant_id'   => $product->tenant_id,
            ]);

            // Create a small set of values with price adjustments
            $values = [
                ['value' => 'Small',  'price_adjustment' => 0],
                ['value' => 'Regular','price_adjustment' => 2000],
                ['value' => 'Large',  'price_adjustment' => 4000],
            ];

            foreach ($values as $v) {
                ProductOptionValue::create([
                    'product_option_id' => $option->id,
                    'value'             => $v['value'],
                    'price_adjustment'  => $v['price_adjustment'],
                    'tenant_id'         => $product->tenant_id,
                ]);
            }
        });
    }
}
