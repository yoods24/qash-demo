<?php

namespace App\Support;

use App\Models\Product;
use App\Services\Customer\DiscountFetcher;

class PriceAfterDiscount
{
    /**
     * Calculate the best discount for a product and return pricing metadata.
     */
    public static function calculate(Product $product, ?string $tenantId, ?float $priceOverride = null): array
    {
        $basePrice = $priceOverride ?? (float) $product->price;
        $result = [
            'has_discount' => false,
            'discount_id' => null,
            'discount_name' => null,
            'discount_type' => null,
            'discount_amount' => 0.0,
            'final_price' => round($basePrice, 2),
            'badge' => null,
        ];

        if (! $tenantId || $basePrice <= 0) {
            return $result;
        }

        $discounts = app(DiscountFetcher::class)->forTenant($tenantId);
        if ($discounts->isEmpty()) {
            return $result;
        }

        $applicable = $discounts->filter(fn ($discount) => $discount->appliesToProduct($product->id));
        if ($applicable->isEmpty()) {
            return $result;
        }

        $bestDiscount = null;
        $bestAmount = 0.0;

        foreach ($applicable as $discount) {
            $amount = $discount->discountAmountFor($basePrice);
            if ($amount > $bestAmount) {
                $bestAmount = $amount;
                $bestDiscount = $discount;
            }
        }

        if (! $bestDiscount || $bestAmount <= 0) {
            return $result;
        }

        $finalPrice = max($basePrice - $bestAmount, 0);

        $badge = $bestDiscount->discount_type === 'percent'
            ? '-' . rtrim(rtrim(number_format((float) $bestDiscount->value, 2, '.', ''), '0'), '.') . '%'
            : '-Rp' . number_format($bestAmount, 0, ',', '.');

        return [
            'has_discount' => true,
            'discount_id' => $bestDiscount->id,
            'discount_name' => $bestDiscount->name,
            'discount_type' => $bestDiscount->discount_type,
            'discount_amount' => round($bestAmount, 2),
            'final_price' => round($finalPrice, 2),
            'badge' => $badge,
        ];
    }
}
