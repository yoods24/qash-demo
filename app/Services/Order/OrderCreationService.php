<?php

namespace App\Services\Order;

use App\Models\Discount;
use App\Models\Order;

class OrderCreationService
{
    /**
     * Decrement discount quantities for decrement-type discounts based on order usage.
     */
    public function handleDiscountUsage(Order $order): void
    {
        $order->loadMissing('items');

        $usage = [];

        foreach ($order->items as $item) {
            if (! $item->discount_id) {
                continue;
            }

            $usage[$item->discount_id] = ($usage[$item->discount_id] ?? 0) + (int) $item->quantity;
        }

        if (empty($usage)) {
            return;
        }

        Discount::query()
            ->whereIn('id', array_keys($usage))
            ->lockForUpdate()
            ->get()
            ->each(function (Discount $discount) use ($usage) {
                if ($discount->quantity_type !== 'decrement') {
                    return;
                }

                $decrementBy = $usage[$discount->id] ?? 0;
                if ($decrementBy <= 0) {
                    return;
                }

                $newQuantity = max(0, (int) $discount->quantity - $decrementBy);
                $discount->forceFill(['quantity' => $newQuantity])->save();
            });
    }
}
