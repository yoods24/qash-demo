<x-customer.layout>
    @php
        $taxLines = $order->taxLines ?? collect();
        if (! $taxLines instanceof \Illuminate\Support\Collection) {
            $taxLines = collect($taxLines);
        }
        $totals = [
            'subtotal' => (float) ($order->subtotal ?? $order->total ?? 0),
            'total_tax' => (float) ($order->total_tax ?? $taxLines->sum('amount')),
            'grand_total' => (float) ($order->grand_total ?? $order->total ?? 0),
            'xendit_fee' => (float) ($order->xendit_fee ?? 0),
            'qash_fee' => (float) ($order->qash_fee ?? 0),
        ];
        $totals['fees_total'] = $totals['xendit_fee'] + $totals['qash_fee'];
        $totals['net'] = $totals['grand_total'] - $totals['fees_total'];
        $resolveOptions = function ($item): array {
            $raw = data_get(
                $item,
                'product_options',
                data_get($item, 'options.options', data_get($item, 'options', []))
            );
            $excludedKeys = ['category', 'description', 'estimated_seconds', 'base_price', 'note'];

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
                $label = is_string($key) ? $key : null;
                $keyLabel = $label ? strtolower($label) : null;

                if ($keyLabel && in_array($keyLabel, $excludedKeys, true)) {
                    continue;
                }

                if (! is_array($option)) {
                    $valueText = (string) $option;
                    if ($valueText === '') {
                        continue;
                    }

                    $lines[] = [
                        'label' => $label ?? __('Option'),
                        'value' => $valueText,
                        'adjustment' => 0,
                    ];

                    continue;
                }

                if (isset($option['option_value']) && is_array($option['option_value'])) {
                    $lineLabel = $option['label'] ?? $option['name'] ?? $option['title'] ?? $label ?? __('Option');
                    $value = $option['option_value']['value'] ?? $option['option_value']['name'] ?? '';
                    $adjustmentValue = (int) ($option['option_value']['price_adjustment'] ?? $option['option_value']['adjustment'] ?? 0);

                    if ($value === '' || in_array(strtolower((string) $lineLabel), $excludedKeys, true)) {
                        continue;
                    }

                    $lines[] = [
                        'label' => $lineLabel,
                        'value' => $value,
                        'adjustment' => $adjustmentValue,
                    ];

                    continue;
                }

                $lineLabel = $option['label'] ?? $option['name'] ?? $option['title'] ?? $option['option'] ?? $option['display_name'] ?? $label ?? __('Option');
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

                if (in_array(strtolower((string) $lineLabel), $excludedKeys, true)) {
                    continue;
                }

                $adjustmentValue = (int) ($option['price_adjustment'] ?? $option['adjustment'] ?? 0);

                $lines[] = [
                    'label' => $lineLabel,
                    'value' => $value,
                    'adjustment' => $adjustmentValue,
                ];
            }

            return $lines;
        };
        $status = strtolower((string) ($order->status ?? 'confirmed'));
        $labels = ['confirmed' => 'Confirmed', 'preparing' => 'Preparing', 'ready' => 'Ready'];
        $steps = [
            'confirmed' => in_array($status, ['confirmed', 'preparing', 'ready', 'completed'], true),
            'preparing' => in_array($status, ['preparing', 'ready', 'completed'], true),
            'ready' => in_array($status, ['ready', 'completed'], true),
        ];
        $currentLabel = $labels[$status] ?? ucfirst($status);
    @endphp
    <livewire:customer.order-tracker-header :order="$order" />

    <style>
        .order-tracking-wrapper .order-tracking-card {
            border-radius: 32px;
        }
        .order-tracking-item {
            gap: 1.25rem;
        }
        .order-tracking-total-line {
            gap: .5rem;
        }
        .order-tracking-actions .btn {
            min-height: 56px;
        }
        @media (max-width: 991.98px) {
            .order-tracking-wrapper .order-tracking-card {
                padding: 1.75rem !important;
            }
        }
        @media (max-width: 767.98px) {
            .order-tracking-wrapper {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .order-tracking-wrapper .order-tracking-card {
                padding: 1.5rem !important;
            }
            .order-tracking-item {
                flex-direction: column;
                align-items: flex-start !important;
            }
            .order-tracking-total-line {
                flex-direction: column;
                align-items: flex-start !important;
            }
            .order-tracking-actions {
                flex-direction: column !important;
            }
        }
        @media (max-width: 575.98px) {
            .order-tracking-wrapper {
                padding-left: .5rem;
                padding-right: .5rem;
            }
            .order-tracking-wrapper .order-tracking-card {
                border-radius: 22px;
            }
            .order-tracking-actions .btn {
                width: 100%;
            }
        }
    </style>

    <div class="container text-black order-tracking-wrapper">
        <div class="rounded-5 border border-light-subtle shadow-sm p-4 p-md-5 mt-4 order-tracking-card">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1">Items</h4>
                    <p class="text-muted mb-0">Review everything included in this order.</p>
                </div>
            </div>

            <div class="bg-light rounded-4 px-4 py-2 fw-semibold text-uppercase small text-muted mb-3">
                Product
            </div>

            <div class="pb-3">
                @foreach ($order->items as $item)
                    @php
                        $unitPrice = (float) ($item->unit_price ?? $item->price ?? 0);
                        $quantity = (int) ($item->quantity ?? 1);
                        $lineTotal = $unitPrice * $quantity;
                        $optionLines = $resolveOptions($item);
                        $itemName = $item->product_name ?? $item->name ?? __('Item');
                    @endphp
                    <div class="py-4 {{ ! $loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start gap-3 order-tracking-item">
                            <div>
                                <p class="fw-semibold mb-1">{{ $itemName }}</p>
                                <p class="text-muted small mb-2">{{ $quantity }} × {{ rupiah($unitPrice) }}</p>
                                @foreach ($optionLines as $line)
                                    @php $adjustment = $line['adjustment'] ?? 0; @endphp
                                    <div class="text-warning small fw-semibold">
                                        {{ $line['label'] }}: {{ $line['value'] }}
                                                    @if ($adjustment > 0)
                                                        (+ {{ rupiah($adjustment) }})
                                                    @elseif ($adjustment < 0)
                                                        (– {{ rupiah(abs($adjustment)) }})
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                                        <div class="text-end">
                                            <p class="fw-bold mb-0">{{ rupiah($lineTotal) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

                        <div class="pt-3 order-tracking-totals">
                            <div class="d-flex justify-content-between py-2 order-tracking-total-line">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-semibold">{{ rupiah($totals['subtotal']) }}</span>
                            </div>
                            @foreach ($taxLines as $taxLine)
                                <div class="d-flex justify-content-between py-2 order-tracking-total-line">
                                    <span class="text-muted">
                                        {{ $taxLine->name ?? $taxLine['name'] ?? 'Tax' }}
                                        @php
                                            $lineType = $taxLine->type ?? $taxLine['type'] ?? null;
                                            $lineRate = $taxLine->rate ?? $taxLine['rate'] ?? null;
                                        @endphp
                                        @if ($lineType === 'percentage' && $lineRate !== null)
                                            ({{ rtrim(rtrim(number_format($lineRate, 2, '.', ''), '0'), '.') }}%)
                                        @endif
                                    </span>
                                    <span>{{ rupiah($taxLine->amount ?? $taxLine['amount'] ?? 0) }}</span>
                                </div>
                            @endforeach
                            <div class="d-flex justify-content-between py-2 order-tracking-total-line">
                                <span class="text-muted">Total Tax</span>
                                <span>{{ rupiah($totals['total_tax']) }}</span>
                            </div>
                            <hr class="my-3">
                            <div class="d-flex justify-content-between align-items-center order-tracking-total-line">
                                <span class="fw-bold">Total (incl. tax)</span>
                                <span class="fw-bold" style="color: #f58220;">{{ rupiah($totals['grand_total']) }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 mt-3 order-tracking-total-line">
                                <span class="text-muted">Xendit Fee</span>
                                <span>{{ rupiah($totals['xendit_fee']) }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 order-tracking-total-line">
                                <span class="text-muted">Platform Fee</span>
                                <span>{{ rupiah($totals['qash_fee']) }}</span>
                            </div>
                            <hr class="my-3">
                            <div class="d-flex justify-content-between align-items-center order-tracking-total-line">
                                <span class="fw-bold">Net Received</span>
                                <span class="fw-bold">{{ rupiah($totals['net']) }}</span>
                            </div>
                        </div>

            <div class="d-flex flex-column flex-md-row gap-3 mt-4 order-tracking-actions">
                <a href="{{ route('payment.success', ['tenant' => request()->route('tenant'), 'order' => $order]) }}"
                    class="btn btn-dark rounded-pill py-3 px-4 flex-fill fw-semibold">
                    View Invoice
                </a>
                <a href="{{ route('cart.page', ['tenant' => request()->route('tenant')]) }}"
                    class="btn btn-outline-dark rounded-pill py-3 px-4 flex-fill fw-semibold">
                    Continue Browsing
                </a>
            </div>
        </div>
    </div>
</x-customer.layout>
