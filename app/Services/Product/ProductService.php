<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function create(array $data, ?UploadedFile $image = null): Product
    {
        $imagePath = $this->storeImage($image);
        $product = Product::create($this->mapAttributes($data, $imagePath));

        $this->createSingleOption($product, $data);
        $this->createOptionGroups($product, $data);

        return $product;
    }

    public function update(Product $product, array $data, ?UploadedFile $image = null): Product
    {
        $imagePath = $this->syncImage($product, $image);
        $product->update($this->mapAttributes($data, $imagePath, $product));

        $this->createSingleOption($product, $data);

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $this->deleteImage($product->product_image);
        $product->delete();
    }

    public function addOption(Product $product, array $data): ProductOption
    {
        return DB::transaction(function () use ($product, $data) {
            $option = ProductOption::create([
                'product_id' => $product->id,
                'name' => $data['option_name'],
            ]);

            foreach ($data['values'] as $valueData) {
                ProductOptionValue::create([
                    'product_option_id' => $option->id,
                    'value' => $valueData['value'],
                    'price_adjustment' => isset($valueData['price_change'])
                        ? (float) $valueData['price_change']
                        : 0,
                ]);
            }

            return $option;
        });
    }

    /**
     * @throws AuthorizationException
     */
    public function deleteOption(Product $product, ProductOption $option): void
    {
        if ($option->product_id !== $product->id) {
            throw new AuthorizationException('Unauthorized');
        }

        $option->values()->delete();
        $option->delete();
    }

    private function mapAttributes(array $data, ?string $imagePath, ?Product $product = null): array
    {
        return [
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'alternate_name' => $data['alternate_name'] ?? null,
            'price' => $data['price'],
            'goods_price' => $data['goods_price'] ?? null,
            'estimated_seconds' => array_key_exists('estimated_minutes', $data)
                ? $this->minutesToSeconds($data['estimated_minutes'])
                : ($product?->estimated_seconds ?? null),
            'product_image' => $imagePath,
            'description' => $data['description'] ?? null,
            'featured' => (bool) ($data['featured'] ?? false),
            'active' => (bool) ($data['active'] ?? false),
            'stock_qty' => (int) ($data['stock_qty'] ?? ($product?->stock_qty ?? 0)),
        ];
    }

    private function createSingleOption(Product $product, array $data): void
    {
        $optionName = trim((string) ($data['option_name'] ?? ''));
        $values = $data['values'] ?? null;

        if ($optionName === '' || ! is_array($values)) {
            return;
        }

        DB::transaction(function () use ($product, $optionName, $values) {
            $option = ProductOption::create([
                'product_id' => $product->id,
                'name' => $optionName,
            ]);

            foreach ($values as $valueData) {
                $value = trim((string) ($valueData['value'] ?? ''));
                if ($value === '') {
                    continue;
                }

                ProductOptionValue::create([
                    'product_option_id' => $option->id,
                    'value' => $value,
                    'price_adjustment' => isset($valueData['price_change'])
                        ? (float) $valueData['price_change']
                        : 0,
                ]);
            }
        });
    }

    private function createOptionGroups(Product $product, array $data): void
    {
        $groups = $data['options'] ?? null;
        if (! is_array($groups)) {
            return;
        }

        DB::transaction(function () use ($product, $groups) {
            foreach ($groups as $group) {
                $name = trim((string) ($group['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $option = ProductOption::create([
                    'product_id' => $product->id,
                    'name' => $name,
                ]);

                $defaultOnly = ! empty($group['default_only']);
                $defaultValue = trim((string) ($group['default_value'] ?? 'Default')) ?: 'Default';

                if ($defaultOnly) {
                    ProductOptionValue::create([
                        'product_option_id' => $option->id,
                        'value' => $defaultValue,
                        'price_adjustment' => 0,
                    ]);
                    continue;
                }

                $values = is_array($group['values'] ?? null) ? $group['values'] : [];
                foreach ($values as $value) {
                    $val = trim((string) ($value['value'] ?? ''));
                    if ($val === '') {
                        continue;
                    }

                    ProductOptionValue::create([
                        'product_option_id' => $option->id,
                        'value' => $val,
                        'price_adjustment' => isset($value['price_change'])
                            ? (float) $value['price_change']
                            : 0,
                    ]);
                }
            }
        });
    }

    private function storeImage(?UploadedFile $image): ?string
    {
        if (! $image) {
            return null;
        }

        return $image->store('products', 'public');
    }

    private function syncImage(Product $product, ?UploadedFile $image): ?string
    {
        if (! $image) {
            return $product->product_image;
        }

        $this->deleteImage($product->product_image);

        return $this->storeImage($image);
    }

    private function deleteImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function minutesToSeconds($minutes): ?int
    {
        if ($minutes === null || $minutes === '') {
            return null;
        }

        return (int) $minutes * 60;
    }
}
