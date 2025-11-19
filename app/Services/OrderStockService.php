<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\Product;

class OrderStockService
{
    public function deduct(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            $product = Product::withoutTenancy()
                ->where('tenant_id', $order->tenant_id)
                ->lockForUpdate()
                ->find($item->product_id);

            if (! $product) {
                throw new InsufficientStockException('Product ' . $item->product_name . ' is no longer available.');
            }

            if ((int) $product->stock_qty < (int) $item->quantity) {
                throw new InsufficientStockException('Insufficient stock for ' . $product->name . '.');
            }

            $product->decrement('stock_qty', (int) $item->quantity);
        }
    }
}
