@php
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
                    'price_adjustment' => 0,
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
                    'price_adjustment' => $adjustmentValue,
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
                'price_adjustment' => $adjustmentValue,
            ];
        }

        return $lines;
    };

    $incomingTotals = isset($totals) && is_array($totals) ? $totals : [];
    $taxLines = $order->taxLines ?? collect();
    if (! $taxLines instanceof \Illuminate\Support\Collection) {
        $taxLines = collect($taxLines);
    }
    $items = $order->items ?? collect();
    if (! $items instanceof \Illuminate\Support\Collection) {
        $items = collect($items);
    }
    $itemsSubtotal = (float) $items->sum(fn ($item) => ((float) ($item->unit_price ?? $item->price ?? 0)) * (int) ($item->quantity ?? 1));
    $itemsDiscount = (float) $items->sum(fn ($item) => ((float) ($item->discount_amount ?? 0)) * (int) ($item->quantity ?? 1));
    $itemsFinal = (float) $items->sum(fn ($item) => ((float) ($item->final_price ?? $item->unit_price ?? $item->price ?? 0)) * (int) ($item->quantity ?? 1));
    $subtotal = (float) ($incomingTotals['subtotal'] ?? ($order->subtotal ?? $order->total ?? 0));
    $preDiscountSubtotal = $itemsSubtotal > 0 ? $itemsSubtotal : $subtotal;
    $discountTotal = $itemsDiscount > 0 ? $itemsDiscount : max($preDiscountSubtotal - $subtotal, 0);
    $subtotalAfterDiscount = max($preDiscountSubtotal - $discountTotal, 0);
    $subtotal = $subtotalAfterDiscount;
    $totalTax = (float) ($incomingTotals['total_tax'] ?? ($order->total_tax ?? $taxLines->sum('amount')));
    $grandTotal = (float) ($incomingTotals['grand_total'] ?? ($order->grand_total ?? $order->total ?? ($subtotal + $totalTax)));
    $xenditFee = (float) ($incomingTotals['xendit_fee'] ?? ($order->xendit_fee ?? 0));
    $qashFee = (float) ($incomingTotals['qash_fee'] ?? ($order->qash_fee ?? 0));
    $feesTotal = (float) ($incomingTotals['fees_total'] ?? ($xenditFee + $qashFee));
    $netReceived = (float) ($incomingTotals['net'] ?? ($grandTotal - $feesTotal));
    $discountNameFor = function ($item) {
        return optional($item->discount)->name;
    };
@endphp

<div class="mt-4">
    <h5 class="fw-semibold mb-3">Items</h5>
    <div class="table-responsive border rounded-4">
        <table class="table align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="fw-semibold text-uppercase small text-muted ps-4">Product</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    @php
                        $unitPrice = (float) ($item->unit_price ?? $item->price ?? 0);
                        $quantity = (int) ($item->quantity ?? 1);
                        $lineOriginal = $unitPrice * $quantity;
                        $lineFinal = (float) ($item->final_price ?? $item->unit_price ?? $item->price ?? 0) * $quantity;
                        $lineDiscount = (float) ($item->discount_amount ?? 0) * $quantity;
                        $optionLines = $resolveOptions($item);
                        $itemName = $item->product_name ?? $item->name ?? __('Item');
                        $discountLabel = $discountNameFor($item);
                    @endphp
                    <tr>
                        <td class="ps-4 py-4" colspan="2">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $itemName }}</div>
                                    <div class="text-muted small">Qty: {{ $quantity }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted small {{ $lineDiscount > 0 ? 'text-decoration-line-through' : '' }}">
                                        Item price: {{ rupiah($lineOriginal) }}
                                    </div>
                                    @if ($lineDiscount > 0)
                                        <div class="text-success small">
                                            Discount ({{ $discountLabel ?? 'Promo' }}): -{{ rupiah($lineDiscount) }}
                                        </div>
                                    @endif
                                    <div class="fw-semibold">{{ rupiah($lineFinal) }}</div>
                                </div>
                            </div>
                            @if (! empty($optionLines))
                                <div class="text-muted small mt-2">
                                    @foreach ($optionLines as $line)
                                        <div>
                                            <span class="fw-semibold">{{ $line['label'] }}</span>: {{ $line['value'] }}
                                            @php $adjustment = $line['adjustment'] ?? 0; @endphp
                                            @if ($adjustment > 0)
                                                (+ {{ rupiah($adjustment) }})
                                            @elseif ($adjustment < 0)
                                                (– {{ rupiah(abs($adjustment)) }})
                                            @else
                                                (None)
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 border-top pt-3">
        <div class="d-flex justify-content-between py-1">
            <span class="text-muted">Subtotal</span>
            <span class="fw-semibold">{{ rupiah($preDiscountSubtotal) }}</span>
        </div>
        <div class="d-flex justify-content-between py-1">
            <span class="text-muted">Discount</span>
            <span class="text-success">- {{ rupiah($discountTotal) }}</span>
        </div>
        <div class="d-flex justify-content-between py-1">
            <span class="text-muted">Subtotal after discount</span>
            <span class="fw-semibold">{{ rupiah($subtotalAfterDiscount) }}</span>
        </div>

        @foreach ($taxLines as $taxLine)
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted">
                    {{ $taxLine->name ?? $taxLine['name'] ?? 'Tax' }}
                    @php
                        $type = $taxLine->type ?? $taxLine['type'] ?? null;
                        $rate = $taxLine->rate ?? $taxLine['rate'] ?? null;
                    @endphp
                    @if ($type === 'percentage' && $rate !== null)
                        ({{ rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.') }}%)
                    @endif
                </span>
                <span>{{ rupiah($taxLine->amount ?? $taxLine['amount'] ?? 0) }}</span>
            </div>
        @endforeach

        <div class="d-flex justify-content-between py-1">
            <span class="text-muted">Total Tax</span>
            <span>{{ rupiah($totalTax) }}</span>
        </div>

        <div class="d-flex justify-content-between py-1 border-top mt-2 pt-2">
            <span class="fw-semibold">Total (incl. tax)</span>
            <span class="fw-bold" style="color: #f58220;">{{ rupiah($grandTotal) }}</span>
        </div>

        <div class="d-flex justify-content-between py-1 mt-3">
            <span class="text-muted">Xendit Fee</span>
            <span>{{ rupiah($xenditFee) }}</span>
        </div>
        <div class="d-flex justify-content-between py-1">
            <span class="text-muted">Platform Fee</span>
            <span>{{ rupiah($qashFee) }}</span>
        </div>
        <div class="d-flex justify-content-between py-1 border-top mt-2 pt-2">
            <span class="fw-semibold">Net Received</span>
            <span class="fw-bold">{{ rupiah($netReceived) }}</span>
        </div>
    </div>
</div>
