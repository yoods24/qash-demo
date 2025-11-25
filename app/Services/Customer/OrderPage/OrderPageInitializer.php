<?php

namespace App\Services\Customer\OrderPage;

use App\Livewire\Customer\OrderPage;
use App\Models\Category;
use App\Models\CustomerDetail;
use App\Models\DiningTable;
use App\Services\Customer\DiscountFetcher;
use App\Services\OrderService;
use Illuminate\Support\Facades\Session;

/**
 * Initializes the OrderPage component with tenant context, categories, discounts,
 * table/session information, and any existing customer profile.
 */
class OrderPageInitializer
{
    public function __construct(
        private readonly DiscountFetcher $discountFetcher
    ) {
    }

    public function initialize(OrderPage $component): void
    {
        $tenant = tenant();
        $component->tenantId = $tenant?->id ?? request()->route('tenant') ?? $component->tenantId;
        $component->tenantName = ($tenant?->data['name'] ?? null) ?: ($tenant?->id ?? '');
        $component->availableDiscounts = $this->discountFetcher->forTenant($component->tenantId);
        $component->categories = Category::all();

        $this->assignTableFromQuery($component);
        $component->orderType = OrderService::currentOrderType();
        $this->hydrateCustomerFromSession($component);
    }

    private function assignTableFromQuery(OrderPage $component): void
    {
        $code = request()->query('code');
        if ($code) {
            $table = DiningTable::where('tenant_id', $component->tenantId)
                ->where('qr_code', $code)
                ->first();

            if ($table) {
                Session::put('dining_table_id', (int) $table->id);
            }

            return;
        }

        $tableId = request()->query('table');
        if ($tableId && is_numeric($tableId)) {
            Session::put('dining_table_id', (int) $tableId);
        }
    }

    private function hydrateCustomerFromSession(OrderPage $component): void
    {
        if (! Session::has('customer_detail_id')) {
            return;
        }

        $customer = CustomerDetail::find(Session::get('customer_detail_id'));
        if (! $customer) {
            return;
        }

        $component->username = $customer->name;
        $component->customerName = $customer->name;
        $component->customerEmail = $customer->email;
        $component->customerGender = $customer->gender;

        $tableId = Session::get('dining_table_id');
        if ($tableId && $customer->dining_table_id !== (int) $tableId) {
            $customer->update(['dining_table_id' => (int) $tableId]);
        }
    }
}
