<?php

namespace App\Http\Controllers;

use App\Services\Discount\DiscountService;
use App\Models\Product;
use App\Models\Discount;
use App\Http\Requests\backoffice\DiscountCreateRequest;
use App\Http\Requests\backoffice\DiscountUpdateRequest;
use App\Services\Discount\DiscountTenantNotificationService;

class DiscountController extends Controller
{
    public function index()
    {
        $tenantId = tenant('id');

        $productOptions = Product::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('backoffice.promo.discount-index', [
            'tenantParam'    => $tenantId,
            'productOptions' => $productOptions,
        ]);
    }

    public function store(
        DiscountCreateRequest $request,
        DiscountService $service,
        DiscountTenantNotificationService $notification
    ) {
        $discount = $service->create($request->validated());
        $notification->created($discount);

        return redirect()
            ->route('backoffice.discounts.index', ['tenant' => tenant('id')])
            ->with('message', 'Discount created successfully.');
    }

    public function edit(Discount $discount)
    {
        $this->ensureTenant($discount);

        return view('backoffice.promo.discount-edit', [
            'discount' => $discount,
        ]);
    }

    public function update(
        DiscountUpdateRequest $request,
        Discount $discount,
        DiscountService $service,
        DiscountTenantNotificationService $notification
    ) {
        $this->ensureTenant($discount);

        $service->update($discount, $request->validated());
        $notification->updated($discount);

        return redirect()
            ->route('backoffice.discounts.index', ['tenant' => tenant('id')])
            ->with('message', 'Discount updated successfully.');
    }

    protected function ensureTenant(Discount $discount)
    {
        $tenantId = tenant('id');

        if ($discount->tenant_id !== $tenantId) {
            abort(403, 'Unauthorized');
        }
    }
}
