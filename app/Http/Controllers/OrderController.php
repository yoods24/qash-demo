<?php

namespace App\Http\Controllers;

use App\Models\CustomerDetail;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index() {
        $orders = Order::with('customerDetail')->paginate(10);
        return view('backoffice.order.index', [
            'orders' => $orders
        ]);
    }

    public function view(Order $order) {
        // Eager load relations for efficient view rendering
        $order->load([
            'customerDetail',
            'customerDetail.diningTable',
            'items.product.options',
            'items.discount',
            'taxLines',
        ]);

        $items = $order->items instanceof \Illuminate\Support\Collection ? $order->items : collect($order->items);

        $lineSummaries = [];
        foreach ($items as $lineItem) {
            $quantity = (int) ($lineItem->quantity ?? 1);
            $baseUnit = (float) ($lineItem->unit_price ?? data_get($lineItem, 'options.base_price', $lineItem->price ?? 0));
            $finalUnit = (float) ($lineItem->final_price ?? $lineItem->price ?? $baseUnit);
            $perUnitDiscount = (float) ($lineItem->discount_amount ?? max($baseUnit - $finalUnit, 0));

            $lineSummaries[$lineItem->id] = [
                'base_unit' => $baseUnit,
                'final_unit' => $finalUnit,
                'quantity' => $quantity,
                'line_discount' => max($perUnitDiscount, 0) * $quantity,
                'per_unit_discount' => max($perUnitDiscount, 0),
            ];
        }

        $preDiscountSubtotal = collect($lineSummaries)->sum(fn ($data) => $data['base_unit'] * $data['quantity']);
        $discountTotal = collect($lineSummaries)->sum(fn ($data) => $data['line_discount']);
        $subtotalAfterDiscount = max($preDiscountSubtotal - $discountTotal, 0);
        $softwareServices = (float) ($order->qash_fee ?? 0);
        $taxTotal = (float) ($order->total_tax ?? 0);
        $grandTotal = (float) ($order->grand_total ?? $order->total ?? ($subtotalAfterDiscount + $softwareServices + $taxTotal));

        $appliedDiscounts = $items->filter(fn ($item) => (float) ($item->discount_amount ?? 0) > 0)
            ->groupBy('discount_id')
            ->map(function ($group) {
                $name = optional($group->first()->discount)->name ?? 'Promo';
                $amount = $group->sum(fn ($item) => (float) ($item->discount_amount ?? 0) * (int) ($item->quantity ?? 1));
                return ['name' => $name, 'amount' => $amount];
            });

        $receiptItems = $items->map(function ($item) use ($lineSummaries) {
            $lineData = $lineSummaries[$item->id] ?? [
                'final_unit' => (float) ($item->final_price ?? $item->price ?? 0),
                'quantity' => (int) ($item->quantity ?? 1),
            ];
            $options = $item->options ?? [];
            if (is_array($options) && array_key_exists('options', $options)) {
                $options = $options['options'] ?? [];
            }
            return [
                'name' => $item->product_name ?? $item->name,
                'quantity' => $lineData['quantity'] ?? 0,
                'unit_price' => $lineData['final_unit'] ?? 0,
                'line_total' => ($lineData['final_unit'] ?? 0) * ($lineData['quantity'] ?? 0),
                'options' => $options,
                'note' => $item->special_instructions ?? null,
            ];
        });

        $receipt = [
            'order_id' => $order->id,
            'reference' => $order->reference_no ?? ('#' . $order->id),
            'order_type' => $order->orderTypeLabel(),
            'paid_at' => optional($order->paid_at ?: $order->created_at)->format('d-m-Y H:i'),
            'customer_name' => $order->customerDetail->name ?? 'Customer',
            'customer_email' => $order->customerDetail->email ?? null,
            'customer_table' => optional(optional($order->customerDetail)->diningTable)->label,
            'items' => $receiptItems,
            'subtotal' => (float) ($order->subtotal ?? $subtotalAfterDiscount),
            'total_tax' => $taxTotal,
            'grand_total' => $grandTotal,
            'received' => null,
            'change' => null,
            'tax_lines' => optional($order->taxLines)->map(fn ($line) => [
                'name' => $line->name,
                'amount' => (float) $line->amount,
                'rate' => $line->rate,
            ])->all() ?? [],
        ];

        return view('backoffice.order.view', [
            'order' => $order,
            'items' => $items,
            'lineSummaries' => $lineSummaries,
            'preDiscountSubtotal' => $preDiscountSubtotal,
            'discountTotal' => $discountTotal,
            'subtotalAfterDiscount' => $subtotalAfterDiscount,
            'softwareServices' => $softwareServices,
            'taxTotal' => $taxTotal,
            'grandTotal' => $grandTotal,
            'appliedDiscounts' => $appliedDiscounts,
            'receipt' => $receipt,
        ]);
    }
}
