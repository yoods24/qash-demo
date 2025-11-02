<?php

declare(strict_types=1);

namespace App\Livewire\Backoffice;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class KitchenOrders extends Component
{
    public string $status = 'all';
    public string $search = '';

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function startPreparing(int $orderId): void
    {
        if (! auth()->user() || ! auth()->user()->can('kitchen_kds_update_order')) {
            return; // silently ignore if not permitted
        }
        $order = Order::query()->where('status', 'confirmed')->find($orderId);
        if ($order) {
            $order->update(['status' => 'preparing']);
        }
    }

    public function markReady(int $orderId): void
    {
        if (! auth()->user() || ! auth()->user()->can('kitchen_kds_confirm_order')) {
            return; // silently ignore if not permitted
        }
        $order = Order::query()->where('status', 'preparing')->find($orderId);
        if ($order) {
            $order->update(['status' => 'ready']);
        }
    }

    protected function mapFilterToStatus(?string $filter): ?string
    {
        return match ($filter) {
            'confirmed' => 'confirmed',
            'preparing' => 'preparing',
            'done' => 'ready',
            default => null,
        };
    }

    protected function buildItemsBoard($orders): array
    {
        $board = [];
        foreach ($orders as $order) {
            foreach ($order->items as $it) {
                $name = (string) $it->product_name;
                $board[$name] = ($board[$name] ?? 0) + (int) $it->quantity;
            }
        }
        arsort($board);
        $result = [];
        foreach ($board as $name => $qty) {
            $result[] = ['name' => $name, 'qty' => $qty];
        }
        return $result;
    }

    public function render()
    {
        $statusForFilter = $this->mapFilterToStatus($this->status);

        $dineInQuery = Order::with(['items'])->latest();
        if ($statusForFilter) {
            $dineInQuery->where('status', $statusForFilter);
        }
        if ($this->search !== '') {
            $term = trim($this->search);
            $dineInQuery->where('id', 'like', "%{$term}%");
        }
        $dineInOrders = $dineInQuery->get();

        $counts = [
            'all' => Order::count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'preparing' => Order::where('status', 'preparing')->count(),
            'done' => Order::where('status', 'ready')->count(),
        ];

        $itemsBoard = $this->buildItemsBoard($dineInOrders);

        $takeawayOrders = collect([
            [
                'id' => 710254,
                'status' => 'ready',
                'token' => '0104',
                'time' => now()->format('h:i A, d-m-Y'),
                'items' => [
                    ['name' => 'Club Sandwich', 'qty' => 1],
                    ['name' => 'Iced Tea', 'qty' => 1],
                ],
            ],
            [
                'id' => 710253,
                'status' => 'preparing',
                'token' => '0103',
                'time' => now()->subMinutes(6)->format('h:i A, d-m-Y'),
                'items' => [
                    ['name' => 'Veg Burger', 'qty' => 1],
                ],
            ],
        ]);

        return view('livewire.backoffice.kitchen-orders', [
            'status' => $this->status,
            'search' => $this->search,
            'counts' => $counts,
            'dineInOrders' => $dineInOrders,
            'takeawayOrders' => $takeawayOrders,
            'itemsBoard' => $itemsBoard,
        ]);
    }
}
