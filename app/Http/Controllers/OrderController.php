<?php

namespace App\Http\Controllers;

use App\Models\CustomerDetail;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index() {
        $orders = Order::with('customerDetail')->paginate(10);
        return view('backoffice.order.index', [
            'orders' => $orders
        ]);
    }

    public function view(Order $order) {
        // Eager load relations for efficient view rendering
        $order->load(['customerDetail', 'items.product.options']);
        return view('backoffice.order.view', ['order' => $order]);
    }
}
