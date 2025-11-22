<?php

namespace App\Services\Customer;

use App\Models\Discount;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DiscountFetcher
{
    /**
     * @var array<string,\Illuminate\Support\Collection>
     */
    protected array $cache = [];

    public function forTenant(?string $tenantId): Collection
    {
        if (! $tenantId) {
            return collect();
        }

        if (! array_key_exists($tenantId, $this->cache)) {
            $this->cache[$tenantId] = $this->load($tenantId);
        }

        return $this->cache[$tenantId];
    }

    protected function load(string $tenantId): Collection
    {
        $today = Carbon::today();

        $discounts = Discount::query()
            ->forTenant($tenantId)
            ->availableForDate($today)
            ->orderBy('valid_till')
            ->get();

        if ($discounts->isEmpty()) {
            return collect();
        }

        $productIds = $discounts->pluck('products')
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();

        $products = $productIds
            ? Product::where('tenant_id', $tenantId)
                ->whereIn('id', $productIds)
                ->get(['id', 'name', 'product_image', 'price'])
            : collect();

        $productsById = $products->keyBy('id');

        return $discounts->map(function (Discount $discount) use ($productsById) {
            $rawProducts = $discount->getAttribute('products');

            if (is_string($rawProducts)) {
                $decoded = json_decode($rawProducts, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $rawProducts = $decoded;
                }
            }

            $items = collect(Arr::wrap($rawProducts))
                ->map(fn ($id) => $productsById->get((int) $id))
                ->filter()
                ->values();

            $discount->available_products = $discount->applicable_for === 'specific'
                ? $items
                : collect();

            return $discount;
        });
    }
}
