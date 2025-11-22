<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #0f172a; }
        .invoice-wrapper { max-width: 800px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px; background: #fff; }
        .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
        .invoice-logo { width: 120px; height: 120px; border-radius: 16px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; background: #fff7ed; }
        .invoice-logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .invoice-meta { text-align: right; }
        .invoice-meta h1 { font-size: 24px; margin: 0 0 8px; color: #ea580c; }
        .muted { color: #64748b; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { text-align: left; padding: 10px; background: #fff7ed; font-size: 12px; color: #c2410c; text-transform: uppercase; letter-spacing: .05em; }
        td { padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .totals { margin-top: 16px; }
        .totals table td { border: 0; padding: 4px 0; }
        .totals table tr.total td { font-weight: 600; font-size: 15px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .footer { margin-top: 32px; text-align: center; font-size: 12px; color: #64748b; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #f97316; color: #fff; font-size: 11px; text-transform: uppercase; }
    </style>
</head>
<body>
@php use Illuminate\Support\Str; @endphp
@php
    $profile = $tenantProfile ?? null;
    $companyName = $profile->brand_heading ?? ($profile->company_name ?? ($tenant->name ?? 'Your Company'));
    $companyAddress = $profile->address ?? 'Address not set';
    $companyPhone = $profile->contact_phone ?? 'Phone unavailable';
    $companyEmail = $profile->contact_email ?? 'Email unavailable';
    $customer = $customer ?? $order->customerDetail;
@endphp
<div class="invoice-wrapper">
    <div class="invoice-header">
        <div>
            <div class="invoice-logo">
                @if(!empty($settings?->invoice_logo))
                    <img src="{{ $settings->invoice_logo }}" alt="Company Logo">
                @else
                    <span>{{ Str::of($companyName)->substr(0, 2)->upper() }}</span>
                @endif
            </div>
            @if($settings?->show_company_details)
                <div class="muted" style="margin-top:12px;">
                    <div>{{ $companyName }}</div>
                    <div>{{ $companyAddress }}</div>
                    <div>{{ $companyPhone }}</div>
                    <div>{{ $companyEmail }}</div>
                </div>
            @endif
        </div>
        <div class="invoice-meta">
            <div class="badge">Paid</div>
            <h1>Invoice</h1>
            <div>Invoice No: <strong>{{ $invoiceNumber }}</strong></div>
            <div>Issue Date: {{ optional($order->created_at)->format('d M Y') }}</div>
            <div>Due Date: {{ optional($dueDate)->format('d M Y') }}</div>
        </div>
    </div>

    <div style="display:flex; justify-content:space-between; margin-bottom:16px; font-size:13px;">
        <div>
            <div class="muted">Bill To</div>
            <div><strong>{{ $customer->name ?? 'Walk-in Customer' }}</strong></div>
            <div>{{ $customer->email ?? 'N/A' }}</div>
        </div>
        <div style="text-align:right;">
            <div class="muted">Reference</div>
            <div>{{ $order->reference_no ?? '-' }}</div>
            <div>Status: {{ ucfirst($order->status ?? 'confirmed') }}</div>
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th style="width:40%;">Item</th>
            <th style="width:20%;">Quantity</th>
            <th style="width:20%;">Price</th>
            <th style="width:20%; text-align:right;">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td>
                    <div>{{ $item->product_name }}</div>
                    @php $options = collect($item->options['options'] ?? $item->options ?? [])->map(function($value, $key){ return is_array($value) ? ($value['value'] ?? '') : $value; })->filter(); @endphp
                    @if($options->isNotEmpty())
                        <small class="muted">{{ $options->implode(', ') }}</small>
                    @endif
                </td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                <td style="text-align:right;">{{ number_format((float) $item->final_price * (int) $item->quantity, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td style="width:70%; text-align:right;" class="muted">Subtotal</td>
                <td style="width:30%; text-align:right;">{{ number_format($totals['subtotal'], 2) }}</td>
            </tr>
            <tr>
                <td style="text-align:right;" class="muted">Discounts</td>
                <td style="text-align:right;">-{{ number_format($totals['discount'], 2) }}</td>
            </tr>
            <tr>
                <td style="text-align:right;" class="muted">Tax</td>
                <td style="text-align:right;">{{ number_format($totals['tax'], 2) }}</td>
            </tr>
            <tr class="total">
                <td style="text-align:right;">Grand Total</td>
                <td style="text-align:right;">{{ number_format($totals['grand_total'], 2) }}</td>
            </tr>
            @if($totals['rounded_total'] !== null)
                <tr>
                    <td style="text-align:right;">Rounded Total</td>
                    <td style="text-align:right;">{{ number_format($totals['rounded_total'], 2) }}</td>
                </tr>
            @endif
        </table>
    </div>

    @if(!empty($settings?->invoice_header_terms))
        <div class="muted" style="margin-top:24px;">
            <strong>Notes:</strong>
            <div>{{ $settings->invoice_header_terms }}</div>
        </div>
    @endif

    <div class="footer">
        @if(!empty($settings?->invoice_footer_terms))
            <div>{{ $settings->invoice_footer_terms }}</div>
        @else
            <div>Thank you for dining with us.</div>
        @endif
    </div>
</div>
</body>
</html>
