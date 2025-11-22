<x-backoffice.layout>
    <style>
        .receipt-container p {
            margin: 0;
        }

    </style>
    @php
        $items = $order->items ?? collect();
        if (! $items instanceof \Illuminate\Support\Collection) {
            $items = collect($items);
        }

        $lineSummaries = [];
        foreach ($items as $lineItem) {
            $quantity = (int) ($lineItem->quantity ?? 1);
            $baseUnit = (float) ($lineItem->unit_price ?? data_get($lineItem, 'options.base_price', $lineItem->price ?? 0));
            $finalUnit = (float) ($lineItem->final_price ?? $lineItem->price ?? $baseUnit);
            $perUnitDiscount = (float) ($lineItem->discount_amount ?? max($baseUnit - $finalUnit, 0));

            $lineSummaries[$lineItem->id] = [
                'base_unit' => $baseUnit,
                'final_unit' => $finalUnit,
                'quantity' => $quantity,
                'line_discount' => max($perUnitDiscount, 0) * $quantity,
                'per_unit_discount' => max($perUnitDiscount, 0),
            ];
        }

        $preDiscountSubtotal = collect($lineSummaries)->sum(fn ($data) => $data['base_unit'] * $data['quantity']);
        $discountTotal = collect($lineSummaries)->sum(fn ($data) => $data['line_discount']);
        $subtotalAfterDiscount = max($preDiscountSubtotal - $discountTotal, 0);
        $softwareServices = (float) ($order->qash_fee ?? 0);
        $taxTotal = (float) ($order->total_tax ?? 0);
        $grandTotal = (float) ($order->grand_total ?? $order->total ?? ($subtotalAfterDiscount + $softwareServices + $taxTotal));

        $appliedDiscounts = $items->filter(fn ($item) => (float) ($item->discount_amount ?? 0) > 0)
            ->groupBy('discount_id')
            ->map(function ($group) {
                $name = optional($group->first()->discount)->name ?? 'Promo';
                $amount = $group->sum(fn ($item) => (float) ($item->discount_amount ?? 0) * (int) ($item->quantity ?? 1));
                return ['name' => $name, 'amount' => $amount];
            });
    @endphp
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('backoffice.order.index')}}">Order</a></li>
        <li class="breadcrumb-item active">Details</li>
      </ol>
    </nav>
    <div class="border rounded shadow p-4">
        <h4 class="mb-3 mb-md-4">Order Details</h4>
        <hr>
        <div class="d-flex flex-wrap justify-content-between">
            {{-- customer details --}}
            <div class="col-lg-3 m-0 mb-4">
                <p class="m-1 bold">{{$order->customerDetail->name}}</p>
                <p class="m-1 bold">{{$order->customerDetail->email}}</p>
                <p class="m-1 bold">{{$order->customerDetail->gender}}</p>
                <p class="m-1 bold">Indonesia</p>
            </div>
            {{-- order details --}}
            <div class="col-lg-4 col-md-6 col-12">
                <div class="card-style text-center">
                    <div class="d-flex justify-content-between">
                        <div class="text-start">
                            <p><strong>Order</strong></p>
                            <p><strong>Order Status</strong></p>
                            <p><strong>Order Date</strong></p>
                            <p><strong>Total Amount</strong></p>
                            <p><strong>Payment Method</strong></p>
                            <p><strong>Additional Info</strong></p>
                        </div>
                        <div class="text-end">
                            <p># {{$order->id}}</p>
                            @if ($order->payment_status ==='paid')
                                <p>
                                    <span class="order-status paid">Paid</span>
                                </p>
                            @elseif ($order->status === 'pending')
                                <p>
                                    <span class="order-status pending">Pending</span>
                                </p>
                            @elseif ($order->status === 'cancelled')
                                <p>
                                    <span class="order-status pending">Cancelled</span>
                                </p>
                            @else
                                <p>
                                    <span class="order-status cancelled">Failed</span>
                                </p>
                            @endif
                            <p>{{$order->created_at}}</p>
                            <p>Rp. {{ number_format($order->grand_total ?? $order->total ?? 0, 0, ',', '.') }}</p>
                            <p>{{ $order->payment_channel }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="border rounded">
            <x-backoffice.table>
                <x-slot name="thead">
                    <th scope="col">Photo</th>
                    <th scope="col">Item</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Price per product</th>
                    <th scope="col">Options</th>
                </x-slot>
                <x-slot name="tbody">
                    @foreach ($order->items as $item)
                        @php
                            $lineData = $lineSummaries[$item->id] ?? [
                                'base_unit' => (float) ($item->unit_price ?? data_get($item, 'options.base_price', $item->price ?? 0)),
                                'final_unit' => (float) ($item->final_price ?? $item->price ?? 0),
                                'quantity' => (int) ($item->quantity ?? 1),
                                'line_discount' => 0,
                                'per_unit_discount' => 0,
                            ];
                            $hasLineDiscount = ($lineData['per_unit_discount'] ?? 0) > 0;
                        @endphp
                        <tr>
                            <td>
                                @php $image = optional($item->product)->product_image; @endphp
                                @if ($image)
                                    <img src="{{ asset('storage/' . $image) }}" alt="{{ $item->product_name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                @else
                                    <span class="text-muted">No image</span>
                                @endif
                            </td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                @if ($hasLineDiscount)
                                    <div class="text-muted text-decoration-line-through">
                                        Rp. {{ number_format($lineData['base_unit'], 0, ',', '.') }}
                                    </div>
                                    <div class="fw-semibold text-success">
                                        Rp. {{ number_format($lineData['final_unit'], 0, ',', '.') }}
                                    </div>
                                    <span class="badge bg-success-subtle text-success">
                                        - {{ rupiah($lineData['line_discount']) }}
                                    </span>
                                @else
                                    Rp. {{ number_format($lineData['base_unit'], 0, ',', '.') }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $opts = $item->options ?? [];
                                    // If saved as attributes array, extract inner 'options'
                                    if (is_array($opts) && array_key_exists('options', $opts)) {
                                        $opts = $opts['options'] ?? [];
                                    }
                                @endphp
                                @if (!empty($opts))
                                    @foreach ($opts as $optionId => $data)
                                        @php
                                            $optionModel = optional(optional($item->product)->options)->firstWhere('id', (int) $optionId);
                                            $optionName = optional($optionModel)->name ?? 'Option';
                                        @endphp
                                        <div>
                                            {{ $optionName }}: {{ $data['value'] ?? '' }}
                                            @if (isset($data['price_adjustment']) && (float) $data['price_adjustment'] > 0)
                                                (+Rp {{ number_format($data['price_adjustment'], 0, ',', '.') }})
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-slot>
            </x-backoffice.table>
            <div class="receipt-container d-flex justify-content-end p-4">
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="card-style text-center">
                        <div class="d-flex justify-content-between">
                            <div class="text-start"><p><strong>Subtotal (before discount):</strong></p></div>
                            <div class="text-start"><p>{{ rupiah($preDiscountSubtotal) }}</p></div>
                        </div>
                        <hr class="my-1">

                        <div class="d-flex justify-content-between">
                            <div class="text-start"><p><strong>Discount:</strong></p></div>
                            <div class="text-start text-success"><p>- {{ rupiah($discountTotal) }}</p></div>
                        </div>
                        <hr class="my-1">

                        <div class="d-flex justify-content-between">
                            <div class="text-start"><p><strong>Subtotal after discount:</strong></p></div>
                            <div class="text-start"><p>{{ rupiah($subtotalAfterDiscount) }}</p></div>
                        </div>
                        <hr class="my-1">

                        <div class="d-flex justify-content-between">
                            <div class="text-start"><p><strong>Software Services:</strong></p></div>
                            <div class="text-start"><p>{{ rupiah($softwareServices) }}</p></div>
                        </div>
                        <hr class="my-1">

                        <div class="d-flex justify-content-between">
                            <div class="text-start"><p><strong>Tax:</strong></p></div>
                            <div class="text-start"><p>{{ rupiah($taxTotal)  }}</p></div>
                        </div>
                        <hr class="my-1">

                        <div class="d-flex justify-content-between">
                            <div class="text-start"><p><strong>Grand Total:</strong></p></div>
                            <div class="text-start"><p>{{ rupiah($grandTotal) }}</p></div>
                        </div>

                        @if($appliedDiscounts->isNotEmpty())
                            <hr class="my-1">
                            <div class="text-start w-100">
                                <p class="fw-semibold mb-1">Applied Discounts</p>
                                <ul class="list-unstyled text-start small mb-0">
                                    @foreach($appliedDiscounts as $discount)
                                        <li>{{ $discount['name'] }} — - {{ rupiah($discount['amount']) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-backoffice.layout>
