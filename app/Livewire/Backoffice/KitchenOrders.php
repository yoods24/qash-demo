<?php

declare(strict_types=1);

namespace App\Livewire\Backoffice;

use App\Models\Order;
use Illuminate\Support\Carbon;
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
        ds('startPreparing called', [
            'orderId' => $orderId,
            'user' => auth()->id(),
            'can' => auth()->user()?->can('kitchen_kds_update_order'),
            'tenant_helper' => function_exists('tenant') ? tenant('id') : null,
            'tenant_param' => request()->route('tenant') ?? null,
        ]);

        if (! auth()->user()?->can('kitchen_kds_update_order')) return;

        $order = Order::find($orderId);
        ds('order fetched', [
            'found' => (bool) $order,
            'id' => $order?->id,
            'status' => $order?->status,
            'tenant_id' => $order?->tenant_id,
        ]);
        if (! $order || $order->status !== 'confirmed') return;

        $now = now();
        $tenantId = function_exists('tenant') ? tenant('id') : null;
        // Persist status + base timestamps first (tenant‑scoped)
        $q0 = \DB::table('orders')->where('id', $orderId);
        if ($tenantId !== null) { $q0->where('tenant_id', $tenantId); }
        $q0->update([
            'status' => 'preparing',
            'preparing_at' => $now,
            'confirmed_at' => $order->confirmed_at ?: ($order->created_at ?: $now),
        ]);
        // Then compute queue_seconds in SQL (preparing_at - confirmed_at)
        $q1 = \DB::table('orders')->where('id', $orderId);
        if ($tenantId !== null) { $q1->where('tenant_id', $tenantId); }
        $q1->update([
            'queue_seconds' => \DB::raw('GREATEST(0, TIMESTAMPDIFF(SECOND, COALESCE(confirmed_at, created_at), preparing_at))'),
        ]);

        $dbQ = \DB::table('orders')
            ->select('id','status','queue_seconds','preparing_at','confirmed_at','tenant_id')
            ->where('id', $orderId);
        if ($tenantId !== null) { $dbQ->where('tenant_id', $tenantId); }
        $db = $dbQ->first();
        ds('db after saving startPreparing', $db);

        $this->dispatch('$refresh');
    }

    public function markReady(int $orderId): void
    {
        ds('markReady called', [
            'orderId' => $orderId,
            'user' => auth()->id(),
            'can' => auth()->user()?->can('kitchen_kds_confirm_order'),
            'tenant_helper' => function_exists('tenant') ? tenant('id') : null,
            'tenant_param' => request()->route('tenant') ?? null,
        ]);

        if (! auth()->user()?->can('kitchen_kds_confirm_order')) {
            return; // silently ignore if not permitted
        }
        $order = Order::find($orderId);
        ds('order fetched', [
            'found' => (bool) $order,
            'id' => $order?->id,
            'status' => $order?->status,
            'tenant_id' => $order?->tenant_id,
        ]);
        if ($order && $order->status === 'preparing') {
            $now = now();
            $tenantId = function_exists('tenant') ? tenant('id') : null;
            // Persist status + timestamps first (tenant‑scoped)
            $qb = \DB::table('orders')->where('id', $orderId);
            if ($tenantId !== null) { $qb->where('tenant_id', $tenantId); }
            $qb->update([
                'status' => 'ready',
                'ready_at' => $now,
                'preparing_at' => $order->preparing_at ?: $now,
                'confirmed_at' => $order->confirmed_at ?: ($order->created_at ?: $now),
            ]);

            // Compute and persist seconds using SQL (tenant‑scoped)
            $q2 = \DB::table('orders')->where('id', $orderId);
            if ($tenantId !== null) { $q2->where('tenant_id', $tenantId); }
            $q2->update([
                'queue_seconds' => \DB::raw('GREATEST(0, TIMESTAMPDIFF(SECOND, COALESCE(confirmed_at, created_at), preparing_at))'),
                'prep_seconds'  => \DB::raw('GREATEST(0, TIMESTAMPDIFF(SECOND, preparing_at, ready_at))'),
                'total_seconds' => \DB::raw('GREATEST(0, TIMESTAMPDIFF(SECOND, COALESCE(confirmed_at, created_at), ready_at))'),
            ]);

            $db2 = \DB::table('orders')
                ->select('id','status','queue_seconds','prep_seconds','total_seconds','preparing_at','confirmed_at','ready_at','tenant_id')
                ->where('id', $orderId);
            if ($tenantId !== null) { $db2->where('tenant_id', $tenantId); }
            $db = $db2->first();
            ds('db after saving markReady', $db);
            $this->dispatch('$refresh');
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
        // Aggregate by product + selected options so variations are visible on the board
        $acc = [];
        foreach ($orders as $order) {
            foreach ($order->items as $it) {
                $pairs = $this->extractOptionPairs($it);
                $optionsDisplay = implode(', ', $pairs);
                $key = $it->product_name . '|' . $optionsDisplay;

                if (!isset($acc[$key])) {
                    $acc[$key] = [
                        'name' => (string) $it->product_name,
                        'options' => $optionsDisplay,
                        'qty' => 0,
                    ];
                }
                $acc[$key]['qty'] += (int) $it->quantity;
            }
        }
        $result = array_values($acc);
        usort($result, fn($a, $b) => $b['qty'] <=> $a['qty']);
        return $result;
    }

    /**
     * Convert an order item's saved options into ["Option Name: Value", ...]
     */
    protected function extractOptionPairs($item): array
    {
        $raw = is_array($item->options) ? $item->options : [];
        if (is_array($raw) && array_key_exists('options', $raw)) {
            $raw = $raw['options'] ?? [];
        }
        if (empty($raw)) return [];

        $pairs = [];
        foreach ($raw as $optId => $data) {
            $optModel = optional(optional($item->product)->options)->firstWhere('id', (int) $optId);
            $optName = $optModel->name ?? 'Option';
            $val = is_array($data) ? ($data['value'] ?? '') : (string) $data;
            if ($val === '') continue;
            $pairs[] = $optName . ': ' . $val;
        }
        return $pairs;
    }

    

    public function render()
    {
        $statusForFilter = $this->mapFilterToStatus($this->status);

        // Build two lists shown simultaneously (no tabs): confirmed and preparing
        $activeOrderTypes = ['dine-in', 'takeaway'];
        $base = Order::with(['items.product.options', 'customerDetail.diningTable'])->latest();
        $term = trim($this->search);
        $confirmedQuery = (clone $base)
            ->where('status', 'confirmed')
            ->whereIn('order_type', $activeOrderTypes);
        $preparingQuery = (clone $base)
            ->where('status', 'preparing')
            ->whereIn('order_type', $activeOrderTypes);
        if ($term !== '') {
            $like = "%{$term}%";
            $confirmedQuery->where('id', 'like', $like);
            $preparingQuery->where('id', 'like', $like);
        }
        $confirmedOrders = $confirmedQuery->get();
        $preparingOrders = $preparingQuery->get();
        $doneQuery = (clone $base)
            ->where('status', 'ready')
            ->whereIn('order_type', $activeOrderTypes)
            ->orderByDesc('ready_at')
            ->orderByDesc('created_at');
        if ($term !== '') {
            $like = "%{$term}%";
            $doneQuery->where('id', 'like', $like);
        }
        $doneOrders = $doneQuery->get();

        $countsBase = Order::whereIn('order_type', $activeOrderTypes);
        $counts = [
            'all' => (clone $countsBase)->count(),
            'confirmed' => (clone $countsBase)->where('status', 'confirmed')->count(),
            'preparing' => (clone $countsBase)->where('status', 'preparing')->count(),
            'done' => (clone $countsBase)->where('status', 'ready')->count(),
        ];
        $counts['active'] = ($counts['confirmed'] ?? 0) + ($counts['preparing'] ?? 0);

        // Build aggregated items board from active orders (confirmed + preparing)
        $itemsBoard = $this->buildItemsBoard(
            $confirmedOrders->concat($preparingOrders)
        );

        // Today overview stats (ready orders only)
        $today = now()->toDateString();
        $todayReady = Order::where('status', 'ready')
            ->whereDate('ready_at', $today)
            ->get();
        $totalCompleted = $todayReady->count();
        $onTime = 0; $lateWarn = 0; $lateDanger = 0;
        foreach ($todayReady as $o) {
            $expected = (int) ($o->expected_seconds_total ?? 0);
            // Prefer stored seconds; fallback to computed
            $total = (int) ($o->total_seconds ?? 0);
            if ($total <= 0) {
                $total = (int) ($o->computed_total_seconds ?? 0);
            }
            if ($total > ($expected + 600)) $lateDanger++;
            elseif ($total > ($expected + 300)) $lateWarn++;
            else $onTime++;
        }
        $todayStats = compact('totalCompleted','onTime','lateWarn','lateDanger');

        return view('livewire.backoffice.kitchen-orders', [
            'status' => $this->status,
            'search' => $this->search,
            'counts' => $counts,
            'confirmedOrders' => $confirmedOrders,
            'preparingOrders' => $preparingOrders,
            'doneOrders' => $doneOrders,
            'itemsBoard' => $itemsBoard,
            'todayStats' => $todayStats,
        ]);
    }
}
