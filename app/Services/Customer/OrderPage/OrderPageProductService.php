<?php

namespace App\Services\Customer\OrderPage;

use App\Models\Category;
use App\Models\Product;
use App\Support\PriceAfterDiscount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Cache\TaggableStore;
use Throwable;

/**
 * Fetches product data, groups it for the UI, and provides price computations
 * with option adjustments and discount metadata.
 */
class OrderPageProductService
{
    private const CACHE_TTL_SECONDS = 300;

    public function loadAllGrouped(?string $tenantId): Collection
    {
        $tenantKey = $this->tenantCacheKey($tenantId);

        return $this->rememberSafely(
            "order_page:products:all:{$tenantKey}",
            function () use ($tenantId) {
                $products = Product::query()
                    ->with('category')
                    ->where('active', 1)
                    ->get();

                $products = $this->withDiscounts($products, $tenantId);

                return $this->groupByCategory($products);
            }
        );
    }

    public function loadByCategory(?string $tenantId, $categoryId): Collection
    {
        $category = Category::find($categoryId);

        if (! $category) {
            return $this->loadAllGrouped($tenantId);
        }

        return $this->rememberSafely(
            "order_page:products:category:{$this->tenantCacheKey($tenantId)}:{$categoryId}",
            function () use ($category, $categoryId, $tenantId) {
                $items = Product::where('category_id', $categoryId)
                    ->where('active', 1)
                    ->get();

                $items = $this->withDiscounts($items, $tenantId);

                return collect([
                    $categoryId => [
                        'id' => $categoryId,
                        'name' => $category->name,
                        'items' => $items,
                    ],
                ]);
            }
        );
    }

    public function loadFeatured(?string $tenantId): Collection
    {
        $tenantKey = $this->tenantCacheKey($tenantId);

        return $this->rememberSafely(
            "order_page:products:featured:{$tenantKey}",
            function () use ($tenantId) {
                $featured = Product::where('featured', 1)
                    ->where('active', 1)
                    ->get();

                return $this->withDiscounts($featured, $tenantId);
            }
        );
    }

    public function withDiscounts(Collection $products, ?string $tenantId): Collection
    {
        return $products->map(function (Product $product) use ($tenantId) {

            $key = "order_page:discount:{$this->tenantCacheKey($tenantId)}:{$product->id}";

            $discount = $this->rememberSafely(
                $key,
                fn () => PriceAfterDiscount::calculate($product, $tenantId)
            );

            $product->setAttribute('discount_details', $discount);

            return $product;
        });
    }

    public function computeUnitPrice(Product $product, array $selectedOptions): float
    {
        $total = (float) $product->price;

        foreach ($product->options as $option) {
            $valueId = $selectedOptions[$option->id] ?? null;
            if (! $valueId) {
                continue;
            }

            $value = $option->values->firstWhere('id', $valueId);
            if ($value) {
                $total += (float) $value->price_adjustment;
            }
        }

        return $total;
    }

    private function groupByCategory(Collection $products): Collection
    {
        return $products
            ->groupBy('category_id')
            ->map(function ($items, $categoryId) {
                return [
                    'id' => $categoryId,
                    'name' => $items->first()->category->name ?? 'Uncategorized',
                    'items' => $items->take(4),
                ];
            });
    }

    private function tenantCacheKey(?string $tenantId): string
    {
        return $tenantId ?: 'public';
    }

    /**
        * Use cache when the store supports tags (required by tenancy-scoped stores);
        * otherwise fall back to uncached execution to avoid "store does not support tagging".
        */
    private function rememberSafely(string $key, callable $callback)
    {
        try {
            if (Cache::getStore() instanceof TaggableStore) {
                return Cache::remember($key, self::CACHE_TTL_SECONDS, $callback);
            }

            return $callback();
        } catch (Throwable $e) {
            if (str_contains(strtolower($e->getMessage()), 'tag')) {
                return $callback();
            }

            throw $e;
        }
    }
}
