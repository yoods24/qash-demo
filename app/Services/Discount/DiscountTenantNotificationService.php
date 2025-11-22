<?php

namespace App\Services\Discount;

use App\Models\Discount;
use App\Models\TenantNotification;

class DiscountTenantNotificationService
{
    public function created(Discount $discount): ?TenantNotification
    {
        return $this->notify($discount, 'Discount Created', sprintf('New discount "%s" is now live.', $discount->name));
    }

    public function updated(Discount $discount): ?TenantNotification
    {
        return $this->notify($discount, 'Discount Updated', sprintf('Discount "%s" has been updated.', $discount->name));
    }

    protected function notify(Discount $discount, string $title, string $description): ?TenantNotification
    {
        try {
            return TenantNotification::create([
                'tenant_id' => $discount->tenant_id,
                'type' => 'discount',
                'title' => $title,
                'description' => $description,
                'item_id' => $discount->id,
                'route_name' => 'backoffice.discounts.index',
                'route_params' => [
                    'tenant' => $discount->tenant_id,
                    'discount' => $discount->id,
                ],
            ]);
        } catch (\Throwable $e) {
            logger()->error('Failed to create discount notification', [
                'discount_id' => $discount->id,
                'action' => $title,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
