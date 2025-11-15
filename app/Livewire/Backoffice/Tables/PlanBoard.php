<?php

declare(strict_types=1);

namespace App\Livewire\Backoffice\Tables;

use App\Models\CustomerDetail;
use App\Models\DiningTable;
use App\Models\Floor;
use App\Models\Order;
use Livewire\Component;

class PlanBoard extends Component
{
    public string|int|null $tenantId = null;

    public ?int $currentFloorId = null;

    // Transfer selection state
    public ?int $fromTableId = null;
    public ?string $fromTableLabel = null;
    public ?string $guestName = null;

    public ?string $flashMessage = null;
    public ?string $flashType = 'success';
    public function boot(): void
    {
        if ($this->tenantId === null) {
            $this->tenantId = request()->route('tenant') ?? (function_exists('tenant') ? tenant('id') : null);
        }
    }
    public function mount(): void
    {
        // Default to first floor if none selected
        $first = Floor::where('tenant_id', $this->tenantId)->orderBy('order')->value('id');
        $this->currentFloorId = $this->currentFloorId ?: ($first ? (int)$first : null);
        if ($this->currentFloorId === null) {
            // Create a default floor if missing
            $f = Floor::create([
                'tenant_id' => $this->tenantId,
                'name' => 'Floor 1',
                'area_type' => 'indoor',
                'order' => 1,
            ]);
            $this->currentFloorId = (int) $f->id;
        }
    }

    public function switchFloor(int $floorId): void
    {
        $this->currentFloorId = $floorId;
        $this->flashMessage = null;
        $this->dispatch('updated');
    }

    public function startTransfer(int $tableId): void
    {
        $t = DiningTable::where('tenant_id', $this->tenantId)->find($tableId);
        if (!$t) return;

        $guest = $this->findCurrentGuestForTable($t->id);

        $this->fromTableId = $t->id;
        $this->fromTableLabel = $t->label;
        $this->guestName = $guest?->name;
        $this->flashMessage = null;
        $this->dispatch('updated');

    }

    public function cancelTransfer(): void
    {
        $this->fromTableId = null;
        $this->fromTableLabel = null;
        $this->guestName = null;
        $this->flashMessage = null;
        $this->dispatch('updated');
    }

    public function moveHere(int $toTableId): void
    {
        if (!$this->fromTableId) return;
        $from = DiningTable::where('tenant_id', $this->tenantId)->findOrFail($this->fromTableId);
        $to = DiningTable::where('tenant_id', $this->tenantId)->findOrFail($toTableId);

        if ($to->status !== 'available') {
            $this->flash('Target table is not available.', 'danger');
            return;
        }

        $customer = $this->findCurrentGuestForTable($from->id);
        if (!$customer) {
            $this->flash('No customer found at the source table.', 'danger');
            $this->dispatch('updated');
            return;
        }

        $customer->update(['dining_table_id' => $to->id]);

        if ($to->status !== 'occupied') $to->update(['status' => 'occupied']);
        if ($from->status !== 'available') $from->update(['status' => 'available']);

        $this->flash(sprintf('Moved %s from %s to %s', $customer->name ?? 'guest', $from->label, $to->label));

        // Clear selection
        $this->cancelTransfer();
        $this->dispatch('updated');
    }

    public function setStatus(int $tableId, string $status): void
    {
        if (!in_array($status, ['available', 'occupied', 'oncleaning'], true)) return;
        $table = DiningTable::where('tenant_id', $this->tenantId)->find($tableId);
        if (!$table) return;
        if ($table->status === $status) return;
        $table->update(['status' => $status]);
        if ($status !== 'occupied') {
            // Do not auto-clear customer mapping; guest display derives from orders.
        }
        $this->flash('Table updated to '.$status);
        $this->dispatch('updated');
    }

    private function flash(string $message, string $type = 'success'): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
    }

    private function findCurrentGuestForTable(int $tableId): ?CustomerDetail
    {
        // Prefer a customer with a recent non-cancelled order at this table
        $customerId = CustomerDetail::where('tenant_id', $this->tenantId)
            ->where('dining_table_id', $tableId)
            ->pluck('id');

        $order = Order::where('tenant_id', $this->tenantId)
            ->whereIn('customer_detail_id', $customerId)
            ->whereNotIn('payment_status', ['cancelled'])
            ->latest('id')
            ->first();

        if ($order?->customerDetail && $order->customerDetail->dining_table_id === $tableId) {
            return $order->customerDetail;
        }

        // Fallback to latest customer detail at the table
        return CustomerDetail::where('tenant_id', $this->tenantId)
            ->where('dining_table_id', $tableId)
            ->latest('id')
            ->first();
    }

    public function render()
    {
        $floors = Floor::where('tenant_id', $this->tenantId)->orderBy('order')->get();

        $tables = DiningTable::where('tenant_id', $this->tenantId)
            ->when($this->currentFloorId, fn($q) => $q->where('floor_id', $this->currentFloorId))
            ->orderBy('id')
            ->get();

        $availableTables = DiningTable::where('tenant_id', $this->tenantId)
            ->when($this->currentFloorId, fn($q) => $q->where('floor_id', $this->currentFloorId))
            ->where('status', 'available')
            ->orderBy('label')
            ->get();

        // Build a tableId => CustomerDetail map using orders to avoid stale entries
        $customerMap = collect();
        $ids = CustomerDetail::where('tenant_id', $this->tenantId)
            ->whereIn('dining_table_id', $tables->pluck('id'))
            ->pluck('id');
        $orders = Order::where('tenant_id', $this->tenantId)
            ->whereIn('customer_detail_id', $ids)
            ->whereNotIn('payment_status', ['cancelled'])
            ->latest('id')
            ->with('customerDetail')
            ->get();
        foreach ($orders as $o) {
            $cd = $o->customerDetail;
            if ($cd && $cd->dining_table_id && !$customerMap->has($cd->dining_table_id)) {
                $customerMap->put($cd->dining_table_id, $cd);
            }
        }

        return view('livewire.backoffice.tables.plan-board', [
            'floors' => $floors,
            'tables' => $tables,
            'availableTables' => $availableTables,
            'customerMap' => $customerMap,
        ]);
    }
}
