<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CustomerOrderLookupController extends Controller
{
    public function showForm(Request $request): View
    {
        $orders = null;
        $name = $request->query('name');
        $email = $request->query('email');

        if (! is_null($name) && ! is_null($email)) {
            $validated = validator(
                ['name' => $name, 'email' => $email],
                [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255'],
                ]
            )->validate();

            $name = trim($validated['name']);
            $email = strtolower($validated['email']);
            $orders = $this->fetchOrders($name, $email);
        }

        return view('customer.orders.check', [
            'orders' => $orders,
            'name' => $name,
            'email' => $email,
        ]);
    }

    public function search(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        return redirect()->route('customer.orders.check', [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);
    }

    private function fetchOrders(string $name, string $email)
    {
        $tenantId = tenant('id');

        return Order::query()
            ->with('customerDetail')
            ->where('tenant_id', $tenantId)
            ->whereHas('customerDetail', function ($query) use ($name, $email) {
                $query->whereRaw('LOWER(email) = ?', [$email])
                    ->where('name', 'like', '%' . $name . '%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }
}
