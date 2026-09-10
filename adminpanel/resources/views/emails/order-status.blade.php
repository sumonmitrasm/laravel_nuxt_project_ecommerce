<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light only"><title>Order update</title></head>
<body style="margin:0;padding:0;background:#f3f6f4;font-family:Arial,Helvetica,sans-serif;color:#17251f;">
@php
    $delivered = $status === 'delivered';
    $heading = $delivered ? 'Your order has been delivered' : 'Your order has been cancelled';
    $intro = $delivered ? 'We hope you enjoy your purchase. Thank you for shopping with us.' : 'Your cancellation has been completed successfully.';
    $accent = $delivered ? '#238553' : '#c43d32';
@endphp
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">{{ $heading }} — {{ $order->order_number }}</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f6f4;width:100%;">
<tr><td align="center" style="padding:32px 14px;">
<table role="presentation" width="620" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:620px;background:#fff;border-radius:14px;box-shadow:0 14px 40px rgba(23,49,38,.10);">
    <tr><td style="padding:20px 38px;"><table role="presentation" width="100%"><tr><td>@if($logoPath)<img src="{{ $message->embed($logoPath) }}" alt="{{ $siteName }}" style="display:block;max-width:165px;max-height:46px;width:auto;height:auto;border:0;">@else<span style="font-size:21px;font-weight:800;">{{ $siteName }}</span>@endif</td><td align="right"><span style="display:inline-block;padding:7px 11px;border-radius:999px;background:#f2f6f4;color:#607068;font-size:10px;font-weight:700;text-transform:uppercase;">Order update</span></td></tr></table></td></tr>
    <tr><td style="background:#173126;padding:32px 38px;color:#fff;">
        <div style="font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#ff9a87;">{{ $delivered ? 'Delivered' : 'Cancelled' }}</div>
        <div style="padding-top:8px;font-size:27px;line-height:35px;font-weight:800;">{{ $heading }}</div>
        <div style="padding-top:10px;font-size:14px;line-height:22px;color:#d7e2dc;">Hi {{ $order->user?->name ?: $order->address?->recipient_name }}, {{ $intro }}</div>
    </td></tr>
    <tr><td style="padding:30px 38px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e3e9e5;border-radius:8px;"><tr><td style="padding:15px;"><div style="font-size:10px;color:#7e8b85;text-transform:uppercase;">Order number</div><div style="padding-top:4px;font-size:16px;font-weight:800;">{{ $order->order_number }}</div></td><td align="right" style="padding:15px;"><span style="color:{{ $accent }};font-size:13px;font-weight:800;text-transform:uppercase;">{{ $status }}</span></td></tr></table>
        <div style="margin:25px 0 10px;font-size:15px;font-weight:800;">Order summary</div>
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
        @foreach($order->items as $item)<tr><td style="padding:12px 0;border-bottom:1px solid #e9eeeb;"><div style="font-size:14px;font-weight:700;">{{ $item->product_name }}</div><div style="padding-top:4px;color:#819088;font-size:12px;">Qty: {{ $item->quantity }}@if($item->sku) &middot; SKU: {{ $item->sku }}@endif</div></td><td align="right" style="padding:12px 0;border-bottom:1px solid #e9eeeb;font-weight:700;">&#2547;{{ number_format((float)$item->line_total,2) }}</td></tr>@endforeach
        </table>
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:18px;"><tr><td style="font-size:16px;font-weight:800;">Order total</td><td align="right" style="color:{{ $accent }};font-size:18px;font-weight:800;">&#2547;{{ number_format((float)$order->grand_total,2) }}</td></tr></table>
        @if(!$delivered && $order->cancellation_reason)<div style="margin-top:24px;padding:15px;background:#fff4f2;border-left:3px solid {{ $accent }};font-size:13px;line-height:20px;"><strong>Cancellation reason</strong><br>{{ $order->cancellation_reason }}</div>@endif
        @if(!$delivered && $order->payment_status === 'refund_pending')<div style="margin-top:12px;color:#66736d;font-size:12px;line-height:19px;">Your payment was received. The refund is now pending and will be processed according to the payment provider's timeline.</div>@endif
        <p style="margin:26px 0 0;color:#8a9690;font-size:12px;line-height:20px;">This is an automated order-status notification.</p>
    </td></tr>
    <tr><td align="center" style="padding:0 20px 22px;color:#929e98;font-size:11px;">&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</td></tr>
</table>
</td></tr></table>
</body>
</html>
