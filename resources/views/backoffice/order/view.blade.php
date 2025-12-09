<x-backoffice.layout>
    <style>
        .receipt-container p {
            margin: 0;
        }

    </style>
    @php
        $items = $items ?? collect();
        if (! $items instanceof \Illuminate\Support\Collection) {
            $items = collect($items);
        }
    @endphp
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('backoffice.order.index')}}">Order</a></li>
        <li class="breadcrumb-item active">Details</li>
      </ol>
    </nav>
    <div class="border rounded shadow p-4">
        <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <h4 class="mb-0">Order Details</h4>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-semibold">Order ID: {{ $order->reference_no ?? ('#' . $order->id) }}</span>
                    @php
                        $payStatus = strtolower($order->payment_status ?? 'pending');
                        $payMap = [
                            'paid' => ['bg' => 'bg-success-subtle text-success', 'label' => 'Paid'],
                            'pending' => ['bg' => 'bg-warning-subtle text-warning', 'label' => 'Pending'],
                            'failed' => ['bg' => 'bg-danger-subtle text-danger', 'label' => 'Failed'],
                            'cancelled' => ['bg' => 'bg-secondary-subtle text-secondary', 'label' => 'Cancelled'],
                        ];
                        $payBadge = $payMap[$payStatus] ?? $payMap['pending'];

                        $status = strtolower($order->status ?? 'pending');
                        $statusMap = [
                            'prepared' => ['bg' => 'bg-primary-subtle text-primary', 'label' => 'Prepared'],
                            'preparing' => ['bg' => 'bg-info-subtle text-info', 'label' => 'Preparing'],
                            'pending' => ['bg' => 'bg-warning-subtle text-warning', 'label' => 'Pending'],
                            'cancelled' => ['bg' => 'bg-secondary-subtle text-secondary', 'label' => 'Cancelled'],
                            'failed' => ['bg' => 'bg-danger-subtle text-danger', 'label' => 'Failed'],
                            'ready' => ['bg' => 'bg-primary-subtle text-primary', 'label' => 'Ready'],
                            'confirmed' => ['bg' => 'bg-success-subtle text-success', 'label' => 'Confirmed'],
                            'paid' => ['bg' => 'bg-success-subtle text-success', 'label' => 'Paid'],
                        ];
                        $statusBadge = $statusMap[$status] ?? $statusMap['pending'];
                    @endphp
                    <span class="badge rounded-pill px-3 py-2 {{ $payBadge['bg'] }}">{{ $payBadge['label'] }}</span>
                    <span class="badge rounded-pill px-3 py-2 {{ $statusBadge['bg'] }}">{{ $statusBadge['label'] }}</span>
                </div>
            </div>
            <button type="button" class="btn btn-main d-flex align-items-center gap-2" onclick="printOrderReceipt()">
                <i class="bi bi-printer"></i>
                <span>Print Receipt</span>
            </button>
        </div>
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
                                @php $imageUrl = optional($item->product)->product_image_url; @endphp
                                @if ($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
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
    <script>
        const orderReceiptHtml = @json(view('backoffice.pos.receipt-print', ['receipt' => $receipt])->render());
        function printOrderReceipt() {
            if (!orderReceiptHtml) return;
            const w = window.open('', '_blank', 'width=480,height=720');
            if (!w) {
                alert('Please allow popups to print the receipt.');
                return;
            }
            w.document.write(orderReceiptHtml);
            w.document.close();
            w.focus();
            w.print();
        }
    </script>
</x-backoffice.layout>
