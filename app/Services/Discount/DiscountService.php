<?php

namespace App\Services\Discount;

use App\Models\Discount;
use Illuminate\Support\Arr;

class DiscountService
{
    public function create(array $data): Discount
    {
        $payload = $this->normalize($data);

        return Discount::create($payload);
    }

    public function update(Discount $discount, array $data): Discount
    {
        $payload = $this->normalize($data);

        $discount->update($payload);

        return $discount;
    }

    public function delete(Discount $discount): void
    {
        $discount->delete();
    }

    /**
     * Normalize all incoming discount data.
     */
    protected function normalize(array $data): array
    {
        // Always attach tenant_id
        $data['tenant_id'] = tenant('id');

        // Normalize products
        if (($data['applicable_for'] ?? 'all') !== 'specific') {
            $data['products'] = null;
        } else {
            $products = Arr::wrap($data['products'] ?? []);
            $products = array_filter($products, fn ($p) => $p !== null && $p !== '');
            $data['products'] = array_values(array_unique(array_map('intval', $products)));
        }

        // Normalize quantity
        if (($data['quantity_type'] ?? 'unlimited') === 'unlimited') {
            $data['quantity'] = null;
        }

        // Normalize days
        if (!empty($data['days']) && is_array($data['days'])) {
            $data['days'] = array_values(array_unique(
                array_map('strtolower', $data['days'])
            ));
        }

        return $data;
    }
}
