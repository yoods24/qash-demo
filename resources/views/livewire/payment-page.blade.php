<div class="container py-5 text-black payment-page-wrapper">
    <style>
        .text-orange { color: #FF8343 !important; }
        .border-orange { border-color: #FF8343 !important; }
        .bg-cream { background-color: #fff7f1; }
        .btn-orange {
            background-color: #FF8343;
            border-color: #FF8343;
            color: #fff;
            box-shadow: 0 12px 30px rgba(255, 131, 67, 0.3);
        }
        .btn-orange:hover:not(:disabled) {
            background-color: #f57632;
            border-color: #f57632;
        }
        .btn-orange:disabled {
            background-color: #ffd1b4;
            border-color: #ffd1b4;
            color: #fff;
            box-shadow: none;
        }
        .payment-card {
            border-radius: 24px;
            box-shadow: 0 20px 45px rgba(28, 25, 23, 0.08);
            border: none;
        }
        .payment-option {
            border-radius: 20px;
            padding: 1.25rem;
            border: 1px solid #e5e5e5;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .payment-option:hover {
            border-color: #ffb48d;
            box-shadow: 0 10px 20px rgba(255, 131, 67, .1);
        }
        .payment-option.active {
            border-color: #FF8343;
            background-color: #fff5ef;
            box-shadow: 0 12px 24px rgba(255, 131, 67, 0.15);
        }
        .payment-option .option-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .divider-line {
            height: 2px;
            width: 100%;
            background-color: #FF8343;
            opacity: .35;
        }
        .items-card hr {
            border-color: rgba(0,0,0,0.05);
            margin: .75rem 0;
        }
        .order-item-row {
            gap: 1rem;
        }
        .payment-total-line {
            gap: .75rem;
        }
        .payment-option-body {
            gap: 1rem;
        }
        @media (max-width: 991.98px) {
            .payment-card {
                border-radius: 18px;
            }
        }
        @media (max-width: 767.98px) {
            .payment-page-wrapper {
                padding-left: .75rem;
                padding-right: .75rem;
            }
            .payment-card {
                padding: 1.5rem !important;
            }
            .payment-option {
                padding: 1rem;
            }
        }
        @media (max-width: 575.98px) {
            .order-item-row {
                flex-direction: column;
                align-items: flex-start !important;
            }
            .order-item-row > div:last-child {
                width: 100%;
                text-align: left;
            }
            .payment-total-line {
                flex-direction: column;
                align-items: flex-start !important;
                text-align: left;
            }
            .payment-option-body {
                flex-wrap: wrap;
            }
            .payment-option-body .flex-grow-1 {
                width: 100%;
            }
            .payment-option-body .bi-check-circle-fill {
                margin-left: 0;
            }
            .payment-page-wrapper .btn {
                font-size: 1rem;
            }
        }
    </style>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="mb-4">
                <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
                    <div>
                        <h1 class="fw-bold display-6 text-dark mb-1">Complete Your Payment</h1>
                        <p class="text-muted mb-1">Reference: <span class="fw-semibold text-orange">{{ $order->reference_no }}</span></p>
                        <p class="text-muted mb-0">Please review your order and choose a payment method.</p>
                    </div>
                    @if($order->status !== 'cancelled' && $order->payment_status !== 'paid')
                        <button class="btn btn-outline-danger ms-md-auto"
                                wire:click="cancelOrder"
                                wire:loading.attr="disabled"
                                wire:target="cancelOrder"
                                @disabled($processing)
                        >
                            <span wire:loading.remove wire:target="cancelOrder">Cancel Order</span>
                            <span wire:loading wire:target="cancelOrder">Cancelling...</span>
                        </button>
                    @endif
                </div>
            </div>

            @if($order->status === 'cancelled')
                <div class="alert alert-warning d-flex align-items-center rounded-4 py-3 px-4">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-4 me-3"></i>
                    <span>This order was cancelled. Start a new order whenever you are ready.</span>
                </div>
            @else
                <div class="alert alert-success d-flex align-items-center rounded-4 py-3 px-4">
                    <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                    <span>Order created. Please choose a payment method.</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger rounded-4">{{ session('error') }}</div>
            @endif

            <div class="row g-4 mt-1">
                <div class="col-lg-6">
                    <div class="bg-white payment-card p-4 items-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="fw-bold mb-1">Order Summary</h4>
                                <div class="divider-line"></div>
                            </div>
                        </div>

                        @php
                            $applicationFee = (float) config('services.xendit.application_fee', 0);
                            $taxLines = $order->taxLines ?? collect();
                            if (! $taxLines instanceof \Illuminate\Support\Collection) {
                                $taxLines = collect($taxLines);
                            }
                            $itemsSubtotal = (float) $order->items->sum(fn($item) => ($item->unit_price ?? 0) * (int) $item->quantity);
                            $itemsDiscount = (float) $order->items->sum(fn($item) => ($item->discount_amount ?? 0) * (int) $item->quantity);
                            $itemsFinal = (float) $order->items->sum(fn($item) => ($item->final_price ?? $item->unit_price ?? 0) * (int) $item->quantity);
                            $calculatedSubtotal = (float) ($order->subtotal ?? $order->total ?? 0);
                            $subtotal = $itemsFinal > 0 ? $itemsFinal : $calculatedSubtotal;
                            $preDiscountSubtotal = $itemsSubtotal > 0 ? $itemsSubtotal : $subtotal;
                            $discountTotal = $itemsDiscount > 0 ? $itemsDiscount : max($preDiscountSubtotal - $subtotal, 0);
                            $totalTax = (float) ($order->total_tax ?? $taxLines->sum('amount'));
                            $grandTotal = (float) ($order->grand_total ?? $order->total ?? ($subtotal + $totalTax));
                            $amountDue = $grandTotal + $applicationFee;

                            $resolveOptions = function ($item): array {
                                $raw = data_get(
                                    $item,
                                    'product_options',
                                    data_get($item, 'options.options', data_get($item, 'options', []))
                                );
                                $excludedKeys = ['category', 'description', 'estimated_seconds', 'base_price', 'note'];
                                $normalizedExcluded = array_map('strtolower', $excludedKeys);

                                if ($raw instanceof \Illuminate\Support\Collection) {
                                    $raw = $raw->toArray();
                                }

                                if (is_array($raw) && array_key_exists('options', $raw) && is_array($raw['options'])) {
                                    $raw = $raw['options'];
                                }

                                if (! is_array($raw)) {
                                    return [];
                                }

                                $lines = [];

                                foreach ($raw as $key => $option) {
                                    $keyLabel = is_string($key) ? strtolower($key) : null;
                                    if ($keyLabel && in_array($keyLabel, $normalizedExcluded, true)) {
                                        continue;
                                    }

                                    if (! is_array($option)) {
                                        $valueText = (string) $option;
                                        if ($valueText === '') {
                                            continue;
                                        }

                                        $label = is_string($key) ? $key : __('Option');
                                        if (in_array(strtolower((string) $label), $normalizedExcluded, true)) {
                                            continue;
                                        }

                                        $lines[] = [
                                            'label' => $label,
                                            'value' => $valueText,
                                            'adjustment' => 0,
                                            'price_adjustment' => 0,
                                        ];
                                        continue;
                                    }

                                    if (isset($option['option_value']) && is_array($option['option_value'])) {
                                        $label = $option['label'] ?? $option['name'] ?? $option['title'] ?? (is_string($key) ? $key : __('Option'));
                                        $value = $option['option_value']['value'] ?? $option['option_value']['name'] ?? '';
                                        $adjustmentValue = (int) ($option['option_value']['price_adjustment'] ?? $option['option_value']['adjustment'] ?? 0);

                                        if ($value === '' || in_array(strtolower((string) $label), $normalizedExcluded, true)) {
                                            continue;
                                        }

                                        $lines[] = [
                                            'label' => $label,
                                            'value' => $value,
                                            'adjustment' => $adjustmentValue,
                                            'price_adjustment' => $adjustmentValue,
                                        ];
                                        continue;
                                    }

                                    $label = $option['label'] ?? $option['name'] ?? $option['title'] ?? $option['option'] ?? $option['display_name'] ?? (is_string($key) ? $key : __('Option'));
                                    $value = $option['value'] ?? $option['values'] ?? $option['selection'] ?? $option['selected'] ?? $option['options'] ?? $option;

                                    $value = collect(\Illuminate\Support\Arr::wrap($value))
                                        ->flatMap(function ($itemValue) {
                                            if (is_array($itemValue)) {
                                                if (isset($itemValue['value'])) {
                                                    return [$itemValue['value']];
                                                }
                                                if (isset($itemValue['name'])) {
                                                    return [$itemValue['name']];
                                                }

                                                return collect($itemValue)
                                                    ->filter(fn ($nestedValue) => is_scalar($nestedValue))
                                                    ->values();
                                            }

                                            return [$itemValue];
                                        })
                                        ->filter(fn ($text) => $text !== null && $text !== '')
                                        ->implode(', ');

                                    if ($value === '') {
                                        continue;
                                    }

                                    if (in_array(strtolower((string) $label), $normalizedExcluded, true)) {
                                        continue;
                                    }

                                    $adjustmentValue = (int) ($option['price_adjustment'] ?? $option['adjustment'] ?? 0);

                                    $lines[] = [
                                        'label' => $label,
                                        'value' => $value,
                                        'adjustment' => $adjustmentValue,
                                        'price_adjustment' => $adjustmentValue,
                                    ];
                                }

                                return $lines;
                            };
                        @endphp

                        <div class="d-flex flex-column">
                            @foreach($order->items as $item)
                                @php
                                    $options = $resolveOptions($item);
                                    $lineOriginal = (float) ($item->unit_price ?? 0) * (int) $item->quantity;
                                    $lineFinal = (float) ($item->final_price ?? $item->unit_price ?? 0) * (int) $item->quantity;
                                    $lineDiscount = (float) ($item->discount_amount ?? 0) * (int) $item->quantity;
                                    $discountName = $item->discount?->name ?? null;
                                @endphp
                                <div class="py-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start order-item-row">
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                            <div class="text-muted small">Qty: {{ $item->quantity }}</div>
                                            @foreach($options as $option)
                                                <div class="text-muted small ms-2">
                                                    - {{ $option['value'] }} 
                                                    @php $adjustment = $option['adjustment'] ?? 0; @endphp
                                                    @if($adjustment > 0)
                                                        (+ {{ rupiahRp($adjustment) }})
                                                    @elseif($adjustment < 0)
                                                        (– {{ rupiahRp(abs($adjustment)) }})
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="text-end">
                                            <div class="text-muted small {{ $lineDiscount > 0 ? 'text-decoration-line-through' : '' }}">
                                                Item price: {{ rupiahRp($lineOriginal) }}
                                            </div>
                                            @if($lineDiscount > 0)
                                                <div class="text-success small">
                                                    Discount ({{ $discountName ?? 'Promo' }}): -{{ rupiahRp($lineDiscount) }}
                                                </div>
                                            @endif
                                            <div class="fw-bold fs-5 text-dark">
                                                Final price: {{ rupiahRp($lineFinal) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-3">
                            <div class="d-flex justify-content-between text-muted small mb-1 payment-total-line">
                                <span>Subtotal</span>
                                <span>{{ rupiahRp($preDiscountSubtotal) }}</span>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mb-1 payment-total-line">
                                <span>Discount</span>
                                <span class="text-success">- {{ rupiahRp($discountTotal) }}</span>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mb-1 payment-total-line">
                                <span>Subtotal after discount</span>
                                <span>{{ rupiahRp($subtotal) }}</span>
                            </div>
                            @foreach ($taxLines as $taxLine)
                                <div class="d-flex justify-content-between text-muted small mb-1 payment-total-line">
                                    <span>
                                        {{ $taxLine->name ?? $taxLine['name'] ?? 'Tax' }}
                                        @php
                                            $lineType = $taxLine->type ?? $taxLine['type'] ?? null;
                                            $lineRate = $taxLine->rate ?? $taxLine['rate'] ?? null;
                                        @endphp
                                        @if ($lineType === 'percentage' && $lineRate !== null)
                                            ({{ rtrim(rtrim(number_format($lineRate, 2, '.', ''), '0'), '.') }}%)
                                        @endif
                                    </span>
                                    <span>{{ rupiahRp($taxLine->amount ?? $taxLine['amount'] ?? 0) }}</span>
                                </div>
                            @endforeach
                            <div class="d-flex justify-content-between text-muted small mb-1 payment-total-line">
                                <span>Total Tax</span>
                                <span>{{ rupiahRp($totalTax) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 payment-total-line">
                                <span class="fw-semibold fs-5">Total (incl. tax)</span>
                                <span class="fw-bold fs-3 text-orange">{{ rupiahRp($grandTotal) }}</span>
                            </div>
                            @if($applicationFee > 0)
                                <hr class="my-3">
                                <div class="d-flex justify-content-between text-muted small mb-1 payment-total-line">
                                    <span>Application Fee</span>
                                    <span>{{ rupiahRp($applicationFee) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 payment-total-line">
                                    <span class="fw-semibold fs-5">Amount to Pay</span>
                                    <span class="fw-bold fs-3 text-orange">{{ rupiahRp($amountDue) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="bg-white payment-card p-4 d-flex flex-column h-100">
                        <div>
                            <h4 class="fw-bold mb-1">Select Payment Method</h4>
                            <div class="divider-line mb-3"></div>
                            <p class="text-muted">Cash payments are confirmed instantly. Digital payments are confirmed via Xendit (QRIS / Card / VA / E-Wallet).</p>
                        </div>

                        <div class="payment-option mb-3 @if($selectedMethod === 'cash') active @endif"
                             wire:click="selectMethod('cash')">
                            <div class="d-flex align-items-center payment-option-body">
                                <div class="option-icon bg-cream text-orange me-3">
                                    <i class="bi bi-cash-coin"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Pay with Cash</div>
                                    <div class="text-muted small">Instant confirmation</div>
                                </div>
                                @if($selectedMethod === 'cash')
                                    <i class="bi bi-check-circle-fill text-orange fs-4"></i>
                                @endif
                            </div>
                        </div>

                        <div class="payment-option mb-4 @if($selectedMethod === 'digital') active @endif"
                             wire:click="selectMethod('digital')">
                            <div class="d-flex align-items-center payment-option-body">
                                <div class="option-icon bg-cream text-orange me-3">
                                    <i class="bi bi-credit-card"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Pay Digitally</div>
                                    <div class="text-muted small">QRIS / Card / VA / E-Wallet</div>
                                </div>
                                @if($selectedMethod === 'digital')
                                    <i class="bi bi-check-circle-fill text-orange fs-4"></i>
                                @endif
                            </div>
                        </div>

                        <div class="mt-auto">
                            <button class="btn btn-orange btn-lg w-100"
                                    wire:click="confirmPayment"
                                    wire:loading.attr="disabled"
                                    wire:target="confirmPayment"
                                    @disabled(! $selectedMethod || $processing || $order->status === 'cancelled')
                            >
                                <span wire:loading.remove wire:target="confirmPayment">
                                    @if($selectedMethod === 'digital')
                                        Confirm Digital Payment
                                    @elseif($selectedMethod === 'cash')
                                        Confirm Cash Payment
                                    @else
                                        Select a Payment Method
                                    @endif
                                </span>
                                <span wire:loading wire:target="confirmPayment">
                                    Processing...
                                </span>
                            </button>
                            @if($order->xendit_invoice_url)
                                <a href="{{ $order->xendit_invoice_url }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-lg w-100 mt-3">
                                    Continue Existing Invoice
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
