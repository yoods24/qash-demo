<?php

declare(strict_types=1);

namespace App\Livewire\Backoffice;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class KitchenOrders extends Component
{
    public function startPreparing(int $orderId): void
    {
        $order = Order::query()->where('status', 'pending')->find($orderId);
        if ($order) {
            $order->update(['status' => 'preparing']);
        }
    }

    public function markReady(int $orderId): void
    {
        $order = Order::query()->where('status', 'preparing')->find($orderId);
        if ($order) {
            $order->update(['status' => 'ready']);
        }
    }

    public function render()
    {
        $newOrders = Order::with(['items'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $inProgress = Order::with(['items'])
            ->where('status', 'preparing')
            ->latest()
            ->get();

        return view('livewire.backoffice.kitchen-orders', [
            'newOrders' => $newOrders,
            'inProgress' => $inProgress,
        ]);
    }
}

