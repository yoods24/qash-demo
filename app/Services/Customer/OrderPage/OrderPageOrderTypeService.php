<?php

namespace App\Services\Customer\OrderPage;

use App\Livewire\Customer\OrderPage;
use App\Models\DiningTable;
use App\Services\OrderService;
use Illuminate\Support\Facades\Session;

/**
 * Centralizes dine-in/takeaway decisions and table synchronization logic.
 */
class OrderPageOrderTypeService
{
    public function syncCurrentTable(OrderPage $component): void
    {
        $tableId = Session::get('dining_table_id');
        $tenantId = $this->resolveTenantId($component);

        if ($tableId && $tenantId) {
            $table = DiningTable::where('tenant_id', $tenantId)->find($tableId);
            $component->currentTable = $table?->label;
            if ($component->currentTable) {
                $this->applyOrderType($component, OrderService::DINE_IN);
                return;
            }
        }

        $component->currentTable = null;
        if ($component->orderType === OrderService::DINE_IN) {
            $this->applyOrderType($component, OrderService::TAKEAWAY);
        }
    }

    public function selectOrderType(OrderPage $component, string $type): void
    {
        if (! in_array($type, OrderService::allowedTypes(), true)) {
            return;
        }

        if ($component->orderType === $type) {
            return;
        }

        $hasTable = (bool) Session::get('dining_table_id');

        if ($type === OrderService::TAKEAWAY && $hasTable) {
            $component->pendingOrderType = $type;
            $component->showSwitchOrderTypeModal = true;
            return;
        }

        if ($type === OrderService::DINE_IN && ! $hasTable) {
            $component->showTableModal = true;
            return;
        }

        $component->pendingOrderType = null;
        $component->showSwitchOrderTypeModal = false;
        $this->applyOrderType($component, $type);
    }

    public function confirmOrderTypeSwitch(OrderPage $component): void
    {
        if ($component->pendingOrderType === OrderService::TAKEAWAY) {
            OrderService::clearTableAssignment(Session::get('customer_detail_id'));
            $component->currentTable = null;
            $this->applyOrderType($component, OrderService::TAKEAWAY);
        }

        $component->pendingOrderType = null;
        $component->showSwitchOrderTypeModal = false;
    }

    public function cancelOrderTypeSwitch(OrderPage $component): void
    {
        $component->pendingOrderType = null;
        $component->showSwitchOrderTypeModal = false;
    }

    private function applyOrderType(OrderPage $component, string $type): void
    {
        if (! in_array($type, OrderService::allowedTypes(), true)) {
            return;
        }

        if ($component->orderType !== $type) {
            $component->orderType = $type;
        }

        OrderService::persistOrderType($type);
    }

    private function resolveTenantId(OrderPage $component): ?string
    {
        return tenant()?->id ?? $component->tenantId ?? request()->route('tenant');
    }
}
