<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Support\OrderItemOptionHydrator;

class OrderTrackingController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        $this->assertTenantOrder($request, $order);

        $order = OrderItemOptionHydrator::hydrate($order);

        return view('payment.order-tracking', [
            'order' => $order,
        ]);
    }

    private function assertTenantOrder(Request $request, Order $order): void
    {
        $tenantId = (string) ($request->route('tenant') ?? tenant('id'));

        if ($order->tenant_id !== $tenantId) {
            abort(404);
        }
    }
}
