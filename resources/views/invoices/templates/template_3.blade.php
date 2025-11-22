<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #0f172a; }
        .invoice-wrapper { max-width: 760px; margin: 0 auto; border: 1px solid #dfe3ea; border-radius: 8px; padding: 24px 28px; background:#fff; }
        .header { text-align:center; margin-bottom:24px; }
        .header h2 { margin:8px 0 4px; font-size: 24px; }
        .header span { color:#475569; font-size: 13px; }
        .details { display:flex; justify-content:space-between; font-size:13px; margin-bottom: 18px; }
        table { width:100%; border-collapse:collapse; }
        th, td { border:1px solid #e2e8f0; padding:10px; font-size:13px; }
        th { background:#f8fafc; text-transform:uppercase; font-size:11px; letter-spacing:.05em; color:#475569; }
        .totals { margin-top:16px; width:300px; margin-left:auto; }
        .totals table td { border:none; padding:6px 0; }
        .totals table tr:last-child td { border-top:1px solid #e2e8f0; font-weight:600; font-size:15px; }
        .terms { margin-top:24px; font-size:12px; color:#475569; }
    </style>
</head>
<body>
@php
    $profile = $tenantProfile ?? null;
    $customer = $customer ?? $order->customerDetail;
    $companyName = $profile->brand_heading ?? ($tenant->name ?? 'Your Company');
@endphp
<div class="invoice-wrapper">
    <div class="header">
        <div style="font-size:12px; color:#475569; text-transform:uppercase;">Receipt</div>
        <h2>{{ $companyName }}</h2>
        <span>Invoice #{{ $invoiceNumber }}</span>
    </div>

    <div class="details">
        <div>
            <div style="font-size:11px; color:#94a3b8; text-transform:uppercase;">Bill To</div>
            <div>{{ $customer->name ?? 'Walk-in Customer' }}</div>
            <div>{{ $customer->email ?? 'N/A' }}</div>
        </div>
        <div>
            <div style="font-size:11px; color:#94a3b8; text-transform:uppercase;">Dates</div>
            <div>Issued {{ optional($order->created_at)->format('d M Y') }}</div>
            <div>Due {{ optional($dueDate)->format('d M Y') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:45%;">Description</th>
                <th style="width:15%;">Qty</th>
                <th style="width:20%;">Unit</th>
                <th style="width:20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>
                        <div>{{ $item->product_name }}</div>
                        @php $options = collect($item->options['options'] ?? $item->options ?? [])->map(function($value){ return is_array($value) ? ($value['value'] ?? '') : $value; })->filter(); @endphp
                        @if($options->isNotEmpty())
                            <small style="color:#94a3b8;">{{ $options->implode(', ') }}</small>
                        @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>{{ number_format((float) $item->final_price * (int) $item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal</td>
                <td style="text-align:right;">{{ number_format($totals['subtotal'], 2) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td style="text-align:right;">-{{ number_format($totals['discount'], 2) }}</td>
            </tr>
            <tr>
                <td>Tax</td>
                <td style="text-align:right;">{{ number_format($totals['tax'], 2) }}</td>
            </tr>
            <tr>
                <td>Total</td>
                <td style="text-align:right;">{{ number_format($totals['grand_total'], 2) }}</td>
            </tr>
            @if($totals['rounded_total'] !== null)
                <tr>
                    <td>Rounded Total</td>
                    <td style="text-align:right;">{{ number_format($totals['rounded_total'], 2) }}</td>
                </tr>
            @endif
        </table>
    </div>

    @if(!empty($settings?->invoice_header_terms))
        <div style="margin-top:18px; padding:12px; border:1px dashed #d4d4d8; border-radius:8px; font-size:12px; color:#475569;">
            {{ $settings->invoice_header_terms }}
        </div>
    @endif

    <div class="terms">
        @if($settings?->invoice_footer_terms)
            {{ $settings->invoice_footer_terms }}
        @else
            All prices include taxes where applicable.
        @endif
    </div>
</div>
</body>
</html>
