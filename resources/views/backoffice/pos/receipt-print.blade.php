<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 16px; color: #0f172a; }
        .receipt-card { width: 360px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; box-shadow: 0 6px 18px rgba(0,0,0,.08); }
        .text-center { text-align: center; }
        .mt-1 { margin-top: 4px; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .fw-bold { font-weight: 700; }
        .fw-semibold { font-weight: 600; }
        .row { display: flex; justify-content: space-between; }
        .border-top { border-top: 1px dashed #cbd5e1; padding-top: 8px; margin-top: 8px; }
        .small { font-size: 12px; color: #475569; }
        .item-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
        .item-name { font-weight: 600; }
        .item-options { margin-left: 12px; color: #64748b; }
        .totals .row { padding: 4px 0; }
        .totals .fw-semibold { font-size: 15px; }
    </style>
</head>
<body>
@php
    $formatMoney = fn($amount) => 'Rp ' . number_format(roundToIndoRupiahTotal($amount ?? 0), 0, ',', '.');
    $receipt = $receipt ?? [];
@endphp
<div class="receipt-card" id="pos-receipt-print">
    <div class="text-center mb-2">
        <div class="fw-bold" style="font-size:18px;">Receipt</div>
        @if(!empty($receipt['reference']))
            <div class="small">Ref: {{ $receipt['reference'] }}</div>
        @endif
        @if(!empty($receipt['paid_at']))
            <div class="small">{{ $receipt['paid_at'] }}</div>
        @endif
    </div>

    @if(!empty($receipt['customer_name']) || !empty($receipt['customer_email']))
        <div class="mb-2">
            <div class="fw-semibold">Customer</div>
            @if(!empty($receipt['customer_name']))
                <div>{{ $receipt['customer_name'] }}</div>
            @endif
            @if(!empty($receipt['customer_email']))
                <div class="small">{{ $receipt['customer_email'] }}</div>
            @endif
            @if(!empty($receipt['order_type']))
                <div class="small">Order Type: {{ $receipt['order_type'] }}</div>
            @endif
            @if(!empty($receipt['customer_table']))
                <div class="small">Table: {{ $receipt['customer_table'] }}</div>
            @endif
        </div>
    @endif

    <div class="mb-3">
        <div class="fw-semibold mb-1">Items</div>
        @foreach(($receipt['items'] ?? []) as $item)
            <div class="item-row">
                <div>
                    <div class="item-name">{{ $item['name'] ?? '' }}</div>
                    <div class="small">Qty {{ $item['quantity'] ?? 0 }} @ {{ $formatMoney($item['unit_price'] ?? 0) }}</div>
                    @if(!empty($item['options']) && is_array($item['options']))
                        <div class="item-options small">
                            @foreach($item['options'] as $opt)
                                @php
                                    $val = is_array($opt) ? ($opt['value'] ?? '') : (string) $opt;
                                    $adj = is_array($opt) && isset($opt['price_adjustment']) ? (float) $opt['price_adjustment'] : null;
                                @endphp
                                <div>- {{ $val }}@if($adj && $adj > 0) (+{{ $formatMoney($adj) }})@endif</div>
                            @endforeach
                        </div>
                    @endif
                    @if(!empty($item['note']))
                        <div class="item-options small">Note: {{ $item['note'] }}</div>
                    @endif
                </div>
                <div class="fw-semibold">{{ $formatMoney($item['line_total'] ?? 0) }}</div>
            </div>
        @endforeach
    </div>

    <div class="totals border-top">
        <div class="row"><span>Subtotal</span><span>{{ $formatMoney($receipt['subtotal'] ?? 0) }}</span></div>
        @foreach(($receipt['tax_lines'] ?? []) as $tax)
            <div class="row small"><span>{{ $tax['name'] ?? 'Tax' }}</span><span>{{ $formatMoney($tax['amount'] ?? 0) }}</span></div>
        @endforeach
        <div class="row fw-semibold"><span>Total</span><span>{{ $formatMoney($receipt['grand_total'] ?? 0) }}</span></div>
        @if(!empty($receipt['received']))
            <div class="row"><span>Received</span><span>{{ $formatMoney($receipt['received']) }}</span></div>
        @endif
        @if(!empty($receipt['change']))
            <div class="row"><span>Change</span><span>{{ $formatMoney($receipt['change']) }}</span></div>
        @endif
    </div>
</div>
</body>
</html>
