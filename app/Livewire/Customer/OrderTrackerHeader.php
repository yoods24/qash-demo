<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use Livewire\Component;

class OrderTrackerHeader extends Component
{
    public Order $order;

    private array $stepDefinitions = [
        'confirmed' => [
            'label' => 'Confirmed',
            'icon' => 'bi-check-circle-fill',
        ],
        'preparing' => [
            'label' => 'Preparing',
            'icon' => 'bi-box-seam-fill',
        ],
        'ready' => [
            'label' => 'Ready',
            'icon' => 'bi-check-circle-fill',
        ],
    ];

    public function render()
    {
        $this->order->refresh();

        $stepKeys = array_keys($this->stepDefinitions);
        $activeKey = in_array($this->order->status, $stepKeys, true)
            ? $this->order->status
            : 'confirmed';

        $activeIndex = array_search($activeKey, $stepKeys, true);
        $activeIndex = $activeIndex === false ? 0 : $activeIndex;

        $steps = [];

        foreach ($stepKeys as $index => $key) {
            $steps[] = [
                'key' => $key,
                'label' => $this->stepDefinitions[$key]['label'],
                'icon' => $this->stepDefinitions[$key]['icon'],
                'state' => $this->determineState($index, $activeIndex),
            ];
        }

        $grandTotal = (float) ($this->order->grand_total ?? $this->order->total ?? 0);
        $xenditFee = (float) ($this->order->xendit_fee ?? 0);
        $qashFee = (float) ($this->order->qash_fee ?? 0);
        $totalFees = $xenditFee + $qashFee;
        $netToTenant = $grandTotal - $totalFees;

        return view('livewire.customer.order-tracker-header', [
            'order' => $this->order,
            'steps' => $steps,
            'activeStatus' => $activeKey,
            'totalFees' => $totalFees,
            'netToTenant' => $netToTenant,
            'grandTotal' => $grandTotal,
        ]);
    }

    private function determineState(int $index, int $activeIndex): string
    {
        if ($index < $activeIndex) {
            return 'done';
        }

        if ($index === $activeIndex) {
            return 'active';
        }

        return 'pending';
    }
}
