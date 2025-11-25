<?php

namespace App\Services\Customer\OrderPage;

use App\Livewire\Customer\OrderPage;
use App\Models\Product;
use App\Models\ProductOptionValue;
use App\Support\CartItemIdentifier;
use App\Support\PriceAfterDiscount;
use Darryldecode\Cart\Facades\CartFacade as Cart;

/**
 * Handles validating selected products and translating them into cart payloads.
 */
class OrderPageCartService
{
    public function __construct(
        private readonly OrderPageProductService $productService
    ) {
    }

    public function addSelectedProductToCart(OrderPage $component): void
    {
        if (! $component->selectedProduct) {
            return;
        }

        $fresh = Product::find($component->selectedProduct->id);
        if (! $fresh || ($fresh->active ?? 1) != 1 || (int) ($fresh->stock_qty ?? 0) <= 0) {
            session()->flash('error', 'This item is unavailable.');
            $component->reset(['selectedProduct', 'selectedOptions', 'quantity', 'showOptionModal', 'selectedProductSoldOut']);
            $component->dispatch('unlock-scroll');
            return;
        }

        $component->selectedProduct->loadMissing('category');

        $options = $this->buildSelectedOptions($component);
        $unitPrice = $this->productService->computeUnitPrice($component->selectedProduct, $component->selectedOptions);

        $payload = $this->buildCartPayload(
            $component->selectedProduct,
            $options,
            $unitPrice,
            $component->quantity,
            $component->note,
            $this->resolveTenantId($component)
        );

        Cart::add($payload);

        $component->reset(['selectedProduct', 'selectedOptions', 'quantity', 'showOptionModal', 'note', 'selectedProductSoldOut']);
        $component->dispatch('unlock-scroll');
        $component->dispatch('cart-updated');
    }

    private function buildSelectedOptions(OrderPage $component): array
    {
        $options = [];

        foreach ($component->selectedOptions as $optionId => $valueId) {
            if (! $valueId) {
                continue;
            }

            $value = ProductOptionValue::find($valueId);
            if (! $value) {
                continue;
            }

            $options[$optionId] = [
                'id' => $value->id,
                'value' => $value->value,
                'price_adjustment' => $value->price_adjustment,
            ];
        }

        return $options;
    }

    private function buildCartPayload(
        Product $product,
        array $options,
        float $price,
        int $quantity,
        ?string $note,
        ?string $tenantId
    ): array {
        $product->loadMissing('category');
        $pricing = PriceAfterDiscount::calculate($product, $tenantId, $price);

        $attributes = [
            'product_id' => $product->id,
            'options' => $options,
            'base_price' => $product->price,
            'raw_price' => $price,
            'image' => $product->product_image ?? null,
            'description' => $product->description ?? null,
            'category' => $product->category->name ?? null,
            'estimated_seconds' => (int) ($product->estimated_seconds ?? 0),
            'note' => $note,
            'discount_id' => $pricing['discount_id'],
            'discount_amount' => $pricing['discount_amount'],
            'discount_name' => $pricing['discount_name'],
            'discount_badge' => $pricing['badge'],
            'final_price' => $pricing['final_price'],
        ];

        return [
            'id' => CartItemIdentifier::make($product->id, $options),
            'name' => $product->name,
            'price' => $pricing['final_price'],
            'quantity' => $quantity,
            'attributes' => $attributes,
        ];
    }

    private function resolveTenantId(OrderPage $component): ?string
    {
        return tenant()?->id ?? $component->tenantId ?? request()->route('tenant');
    }
}
