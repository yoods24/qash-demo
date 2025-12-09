<?php

declare(strict_types=1);

namespace App\Livewire\Backoffice;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Livewire\Component;

class WaiterOrdersPage extends Component
{
    public string|int|null $tenantId = null;

    public string $orderSearch = '';

    /** @var array<int, array<string, mixed>> */
    public array $readyOrders = [];

    /** @var array<int, array<string, mixed>> */
    public array $inProgressOrders = [];

    /** @var array<string, int> */
    public array $stats = [
        'ready' => 0,
        'preparing' => 0,
        'queue' => 0,
        'served' => 0,
    ];

    public function mount(): void
    {
        if ($this->tenantId === null) {
            $this->tenantId = request()->route('tenant') ?? (function_exists('tenant') ? tenant('id') : null);
        }

        $this->refreshOrders();
    }

    public function updatedOrderSearch(): void
    {
        $this->refreshOrders();
    }

    public function refreshOrders(): void
    {
        $tenantId = $this->resolveTenantId();
        $term = trim($this->orderSearch);

        $baseQuery = Order::query()
            ->with([
                'items:id,order_id,product_name,quantity,options,special_instructions',
                'customerDetail:id,name,email,dining_table_id',
                'customerDetail.diningTable:id,label',
            ])
            ->whereIn('order_type', ['dine-in', 'takeaway']);

        if ($tenantId !== null) {
            $baseQuery->where('tenant_id', $tenantId);
        }

        if ($term !== '') {
            $like = '%' . $term . '%';
            $baseQuery->where(function ($q) use ($like) {
                $q->where('reference_no', 'like', $like)
                    ->orWhere('id', 'like', $like)
                    ->orWhereHas('customerDetail', function ($cq) use ($like) {
                        $cq->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    });
            });
        }

        $ready = (clone $baseQuery)
            ->where('status', 'ready')
            ->orderByDesc('ready_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $inProgress = (clone $baseQuery)
            ->whereIn('status', ['confirmed', 'preparing'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $this->readyOrders = $ready->map(fn (Order $order) => $this->mapOrder($order))->all();
        $this->inProgressOrders = $inProgress->map(fn (Order $order) => $this->mapOrder($order))->all();

        $statsBase = Order::query()->whereIn('order_type', ['dine-in', 'takeaway']);
        if ($tenantId !== null) {
            $statsBase->where('tenant_id', $tenantId);
        }

        $this->stats = [
            'ready' => (clone $statsBase)->where('status', 'ready')->count(),
            'preparing' => (clone $statsBase)->where('status', 'preparing')->count(),
            'queue' => (clone $statsBase)->where('status', 'confirmed')->count(),
            'served' => (clone $statsBase)
                ->whereIn('status', ['completed', 'served'])
                ->whereDate('updated_at', now()->toDateString())
                ->count(),
        ];
    }

    public function markAsServed(int $orderId): void
    {
        $tenantId = $this->resolveTenantId();

        $orderQuery = Order::query()->where('id', $orderId);
        if ($tenantId !== null) {
            $orderQuery->where('tenant_id', $tenantId);
        }

        $order = $orderQuery->first();

        if (! $order || ! in_array($order->status, ['ready', 'preparing', 'confirmed'], true)) {
            return;
        }

        $order->status = 'served';
        $order->save();

        session()->flash('message', 'Order marked as served.');
        $this->refreshOrders();
    }

    public function render()
    {
        return view('livewire.backoffice.waiter-orders-page')
            ->layout('components.backoffice.layout');
    }

    protected function resolveTenantId(): string|int|null
    {
        return $this->tenantId ?? (function_exists('tenant') ? tenant('id') : null);
    }

    protected function mapOrder(Order $order): array
    {
        $tableLabel = $order->customerDetail?->diningTable?->label;

        $items = [];
        $itemsCount = 0;
        $hasNote = false;

        foreach ($order->items as $item) {
            $note = trim((string) ($item->special_instructions ?? ''));
            $items[] = [
                'product_name' => (string) $item->product_name,
                'quantity' => (int) $item->quantity,
                'has_note' => $note !== '',
                'note' => $note,
            ];
            $itemsCount += (int) $item->quantity;
            $hasNote = $hasNote || $note !== '';
        }

        $createdAt = $order->created_at ?? now();
        $elapsed = $this->formatElapsed($createdAt);

        return [
            'id' => (int) $order->id,
            'reference' => $order->reference_no ?: str_pad((string) $order->id, 7, '0', STR_PAD_LEFT),
            'order_type' => $order->orderTypeLabel(),
            'order_mode' => ($order->order_type ?? '') === 'takeaway' ? 'Takeaway' : 'Dine-In',
            'table_label' => $tableLabel ? (string) $tableLabel : null,
            'customer_name' => $order->customerDetail?->name,
            'customer_email' => $order->customerDetail?->email,
            'created_at' => $createdAt,
            'created_at_ts' => $createdAt instanceof Carbon ? $createdAt->getTimestamp() : null,
            'elapsed_time' => $elapsed,
            'status' => (string) $order->status,
            'items' => $items,
            'items_count' => $itemsCount,
            'items_line_count' => count($items),
            'has_note' => $hasNote,
        ];
    }

    protected function formatElapsed(Carbon $createdAt): string
    {
        // gmdate in PHP 8.3 expects an int timestamp; ensure we coerce to int to avoid type errors.
        $seconds = (int) max(0, $createdAt->diffInSeconds(now()));

        return $seconds >= 3600
            ? gmdate('H:i:s', $seconds)
            : gmdate('i:s', $seconds);
    }
}
