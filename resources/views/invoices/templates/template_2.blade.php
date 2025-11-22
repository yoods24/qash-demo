<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #0f172a; background: #f8fafc; }
        .invoice-wrapper { max-width: 820px; margin: 0 auto; background: #fff; border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0; }
        .top-bar { background: linear-gradient(90deg, #0f172a, #2563eb); color: #fff; padding: 28px 36px; display:flex; justify-content:space-between; }
        .top-bar h1 { margin: 0; font-size: 26px; letter-spacing: .05em; }
        .company-info { font-size: 12px; line-height: 1.6; }
        .content { padding: 32px 36px; }
        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; font-size: 13px; margin-bottom: 24px; }
        .info-card { background: #f1f5f9; padding: 12px; border-radius: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { text-align: left; padding: 12px; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: #475569; border-bottom: 2px solid #e2e8f0; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .totals { margin-top: 16px; display:flex; justify-content:flex-end; }
        .totals table { width: 320px; }
        .badge { display:inline-flex; align-items:center; gap:8px; font-size: 12px; background:#1e293b; padding:6px 12px; border-radius: 999px; color:#fff; }
        .footer { background: #0f172a; color: #cbd5f5; text-align: center; padding: 16px; font-size: 12px; }
    </style>
</head>
<body>
@php
    $profile = $tenantProfile ?? null;
    $customer = $customer ?? $order->customerDetail;
    $companyName = $profile->brand_heading ?? ($tenant->name ?? 'Your Company');
@endphp
<div class="invoice-wrapper">
    <div class="top-bar">
        <div>
            <h1>{{ $companyName }}</h1>
            @if($settings?->show_company_details)
                <div class="company-info">
                    <div>{{ $profile->address ?? 'Address not provided' }}</div>
                    <div>{{ $profile->contact_phone ?? '' }}</div>
                    <div>{{ $profile->contact_email ?? '' }}</div>
                </div>
            @endif
        </div>
        <div style="text-align:right;">
            @if(!empty($settings?->invoice_logo))
                <img src="{{ $settings->invoice_logo }}" alt="Logo" style="max-height:70px;">
            @endif
            <div class="badge" style="margin-top:12px;">Invoice #{{ $invoiceNumber }}</div>
        </div>
    </div>

    <div class="content">
        <div class="info-grid">
            <div class="info-card">
                <div style="font-size:11px; text-transform:uppercase; color:#475569;">Billed To</div>
                <div style="font-weight:600;">{{ $customer->name ?? 'Walk-in Customer' }}</div>
                <div>{{ $customer->email ?? 'N/A' }}</div>
            </div>
            <div class="info-card">
                <div style="font-size:11px; text-transform:uppercase; color:#475569;">Invoice Date</div>
                <div>{{ optional($order->created_at)->format('d M Y') }}</div>
                <div>Due {{ optional($dueDate)->format('d M Y') }}</div>
            </div>
            <div class="info-card">
                <div style="font-size:11px; text-transform:uppercase; color:#475569;">Payment</div>
                <div>Status: {{ ucfirst($order->payment_status ?? 'pending') }}</div>
                <div>Channel: {{ $order->payment_channel ?? '-' }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="width: 15%;">Qty</th>
                    <th style="width: 20%;">Unit Price</th>
                    <th style="width: 20%; text-align:right;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>
                            <div>{{ $item->product_name }}</div>
                            @php $options = collect($item->options['options'] ?? $item->options ?? [])->map(function($value){ return is_array($value) ? ($value['value'] ?? '') : $value; })->filter(); @endphp
                            @if($options->isNotEmpty())
                                <small style="color:#475569;">{{ $options->implode(', ') }}</small>
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
                    <td style="text-align:left; color:#475569;">Subtotal</td>
                    <td style="text-align:right;">{{ number_format($totals['subtotal'], 2) }}</td>
                </tr>
                <tr>
                    <td style="text-align:left; color:#475569;">Discount</td>
                    <td style="text-align:right;">-{{ number_format($totals['discount'], 2) }}</td>
                </tr>
                <tr>
                    <td style="text-align:left; color:#475569;">Tax</td>
                    <td style="text-align:right;">{{ number_format($totals['tax'], 2) }}</td>
                </tr>
                <tr>
                    <td style="text-align:left; font-weight:600;">Grand Total</td>
                    <td style="text-align:right; font-weight:600;">{{ number_format($totals['grand_total'], 2) }}</td>
                </tr>
                @if($totals['rounded_total'] !== null)
                    <tr>
                        <td style="text-align:left;">Rounded Total</td>
                        <td style="text-align:right;">{{ number_format($totals['rounded_total'], 2) }}</td>
                    </tr>
                @endif
            </table>
        </div>

        @if(!empty($settings?->invoice_header_terms))
            <div style="margin-top:24px; padding:16px; border:1px dashed #cbd5f5; border-radius:12px; background:#f8fafc;">
                <strong>Important:</strong>
                <div style="font-size:13px;">{{ $settings->invoice_header_terms }}</div>
            </div>
        @endif
    </div>

    <div class="footer">
        @if(!empty($settings?->invoice_footer_terms))
            {{ $settings->invoice_footer_terms }}
        @else
            Thank you for supporting our restaurant.
        @endif
    </div>
</div>
</body>
</html>
