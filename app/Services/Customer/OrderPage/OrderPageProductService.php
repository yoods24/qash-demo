<?php

namespace App\Services\Customer\OrderPage;

use App\Livewire\Customer\OrderPage;
use App\Models\Category;
use App\Models\Product;
use App\Support\PriceAfterDiscount;
use Illuminate\Support\Collection;

/**
 * Fetches product data, groups it for the UI, and provides price computations
 * with option adjustments and discount metadata.
 */
class OrderPageProductService
{
    public function loadAllGrouped(?string $tenantId): Collection
    {
        $products = Product::with('category')
            ->where('active', 1)
            ->get();

        $products = $this->withDiscounts($products, $tenantId);

        return $this->groupByCategory($products);
    }

    public function loadByCategory(?string $tenantId, $categoryId): Collection
    {
        $category = Category::find($categoryId);

        if (! $category) {
            return $this->loadAllGrouped($tenantId);
        }

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

    public function loadFeatured(?string $tenantId): Collection
    {
        $featured = Product::where('featured', 1)
            ->where('active', 1)
            ->get();

        return $this->withDiscounts($featured, $tenantId);
    }

    public function withDiscounts(Collection $products, ?string $tenantId): Collection
    {
        return $products->map(function (Product $product) use ($tenantId) {
            $product->setAttribute(
                'discount_details',
                PriceAfterDiscount::calculate($product, $tenantId)
            );

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

    public function prepareSelectedProduct(OrderPage $component, int $productId): void
    {
        $component->selectedProduct = Product::with(['options.values', 'category'])->findOrFail($productId);
        $component->selectedProductSoldOut = (int) ($component->selectedProduct->stock_qty ?? 0) <= 0;

        $component->selectedOptions = [];
        foreach ($component->selectedProduct->options as $option) {
            $component->selectedOptions[$option->id] = null;
        }

        $component->selectedProduct->setAttribute(
            'discount_details',
            PriceAfterDiscount::calculate($component->selectedProduct, $this->resolveTenantId($component))
        );

        $component->quantity = $component->selectedProductSoldOut ? 0 : 1;
        $component->note = '';
        $component->showOptionModal = true;

        $component->dispatch('lock-scroll');
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

    private function resolveTenantId(OrderPage $component): ?string
    {
        return tenant()?->id ?? $component->tenantId ?? request()->route('tenant');
    }
}
