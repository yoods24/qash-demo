<?php

namespace App\Support;

use App\Models\Order;

class OrderItemOptionHydrator
{
    public static function hydrate(Order $order): Order
    {
        $order->loadMissing(['items.discount', 'customerDetail', 'taxLines']);

        $order->items->transform(function ($item) {
            $options = $item->product_options ?? $item->options ?? [];

            if ($options instanceof \Illuminate\Support\Collection) {
                $options = $options->toArray();
            }

            $item->product_options = is_array($options) ? $options : (array) $options;

            return $item;
        });

        return $order;
    }
}
