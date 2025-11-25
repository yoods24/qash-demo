<?php

namespace App\Services\Customer\OrderPage;

use App\Livewire\Customer\OrderPage;
use App\Models\CustomerDetail;
use App\Services\OrderService;
use Illuminate\Support\Facades\Session;

/**
 * Manages the customer modal workflow, including validation and persistence.
 */
class OrderPageCustomerService
{
    public function saveCustomer(OrderPage $component): void
    {
        $data = $component->validate($this->rules());

        $gender = ($data['customerGender'] ?? null) === 'none'
            ? null
            : ($data['customerGender'] ?? null);

        $tenantId = $this->resolveTenantId($component);
        $defaults = [
            'name' => $data['customerName'],
            'gender' => $gender,
            'tenant_id' => $tenantId,
        ];

        $tableId = Session::get('dining_table_id');
        if ($component->orderType === OrderService::DINE_IN && $tableId) {
            $defaults['dining_table_id'] = (int) $tableId;
        }

        $customer = CustomerDetail::firstOrCreate(
            ['email' => $data['customerEmail'], 'tenant_id' => $tenantId],
            $defaults
        );

        $updates = [];
        if ($customer->name !== $data['customerName']) {
            $updates['name'] = $data['customerName'];
        }
        if (($customer->gender ?? null) !== $gender) {
            $updates['gender'] = $gender;
        }
        if ($component->orderType === OrderService::DINE_IN && $tableId && $customer->dining_table_id !== (int) $tableId) {
            $updates['dining_table_id'] = (int) $tableId;
        } elseif ($component->orderType === OrderService::TAKEAWAY && $customer->dining_table_id !== null) {
            $updates['dining_table_id'] = null;
        }
        if (! empty($updates)) {
            $customer->update($updates);
        }

        Session::put('customer_detail_id', $customer->id);
        $component->username = $customer->name;
        $component->customerName = $customer->name;
        $component->customerEmail = $customer->email;
        $component->customerGender = $gender;

        $component->showCustomerModal = false;
    }

    public function populateCustomerForEdit(OrderPage $component): void
    {
        $customer = Session::has('customer_detail_id')
            ? CustomerDetail::find(Session::get('customer_detail_id'))
            : null;

        if ($customer) {
            $component->customerName = $customer->name;
            $component->customerEmail = $customer->email;
            $component->customerGender = $customer->gender;
        }

        $component->pendingAction = 'update_profile';
        $component->showCustomerModal = true;
    }

    private function rules(): array
    {
        return [
            'customerName' => 'required|string|max:255',
            'customerEmail' => 'required|email|max:255',
            'customerGender' => 'nullable|in:male,female,none',
        ];
    }

    private function resolveTenantId(OrderPage $component): ?string
    {
        return tenant()?->id ?? $component->tenantId ?? request()->route('tenant');
    }
}
