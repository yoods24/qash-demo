<?php

namespace App\Observers;

use App\Models\DiningTable;
use App\Models\Order;

class OrderObserver
{
    /**
     * When an order is created, if it's already paid, mark the table occupied.
     */
    public function created(Order $order): void
    {
        $this->updateTableStatusIfPaid($order);
    }

    /**
     * When an order is updated, if payment_status transitioned to paid, mark occupied.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('payment_status')) {
            $this->updateTableStatusIfPaid($order);
        }
    }

    private function updateTableStatusIfPaid(Order $order): void
    {
        if (($order->payment_status ?? '') !== 'paid') {
            return;
        }

        $customer = $order->customerDetail;
        if (!$customer || !$customer->dining_table_id) {
            return;
        }

        $tenantId = (string) ($order->tenant_id ?? tenant('id'));
        $table = DiningTable::where('tenant_id', $tenantId)
            ->find($customer->dining_table_id);

        if ($table && $table->status !== 'occupied') {
            $table->update(['status' => 'occupied']);
        }
    }
}

