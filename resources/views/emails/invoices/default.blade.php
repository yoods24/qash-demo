@php
    $customer = $order->customerDetail;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #0f172a; }
        .container { max-width: 560px; margin: 0 auto; padding: 32px; border: 1px solid #eaeaea; border-radius: 12px; }
        .btn { display: inline-block; padding: 12px 18px; background-color: #f97316; color: #fff; text-decoration: none; border-radius: 8px; margin: 16px 0; }
        p { line-height: 1.5; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="margin-top:0;">Hi {{ $customer->name ?? 'there' }},</h2>
        <p>Thanks for ordering with {{ $tenantName }}. Your receipt (Invoice #{{ $invoiceNumber }}) is attached to this email.</p>
        <p>Please keep this document for your records. If you have any questions or need adjustments, simply reply to this email and our team will help.</p>
        <p>We hope to see you again soon!</p>
        <p style="margin-top:24px;">Cheers,<br>{{ $tenantName }}</p>
    </div>
</body>
</html>
