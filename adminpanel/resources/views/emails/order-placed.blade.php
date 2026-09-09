<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="color-scheme" content="light only">
    <title>Order confirmation</title>
</head>
<body style="margin:0;padding:0;background:#f3f6f4;font-family:Arial,Helvetica,sans-serif;color:#17251f;">
@php($siteName = $generalSetting->side_name ?? config('app.name', 'NovaCart'))
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">Your order {{ $order->order_number }} has been received.</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f6f4;width:100%;">
    <tr><td align="center" style="padding:32px 14px;">
        <table role="presentation" width="620" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:620px;background:#ffffff;border-radius:14px;box-shadow:0 14px 40px rgba(23,49,38,.10);">
            <tr><td style="background:#ffffff;padding:20px 38px;border-radius:14px 14px 0 0;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td valign="middle">@if($logoPath)<img src="{{ $message->embed($logoPath) }}" alt="{{ $siteName }}" style="display:block;width:auto;max-width:165px;height:auto;max-height:46px;border:0;">@else<span style="font-size:21px;font-weight:800;color:#14251d;">{{ $siteName }}</span>@endif</td><td align="right" valign="middle"><span style="display:inline-block;padding:7px 11px;border-radius:999px;background:#f2f6f4;color:#607068;font-size:10px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;">Order confirmation</span></td></tr></table></td></tr>
            <tr><td style="background:#173126;padding:32px 38px;color:#fff;">
                <div style="font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#ff9a87;">Order received</div>
                <div style="padding-top:8px;font-size:28px;line-height:35px;font-weight:800;">Thank you for your order</div>
                <div style="padding-top:10px;font-size:14px;line-height:22px;color:#d7e2dc;">Hi {{ $order->user->name }}, we have received your order and will keep you updated.</div>
            </td></tr>
            <tr><td style="background:#fff;padding:32px 38px;border-radius:0 0 14px 14px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:26px;background:#fff6f3;border:1px solid #ffdcd4;border-radius:8px;">
                    <tr>
                        <td style="padding:14px 16px;"><div style="font-size:11px;color:#8b756d;text-transform:uppercase;">Order number</div><div style="padding-top:4px;font-size:16px;font-weight:800;color:#17251f;">{{ $order->order_number }}</div></td>
                        <td align="right" style="padding:14px 16px;"><div style="font-size:11px;color:#8b756d;text-transform:uppercase;">Placed on</div><div style="padding-top:4px;font-size:13px;font-weight:700;color:#17251f;">{{ $order->placed_at->format('d M Y, h:i A') }}</div></td>
                    </tr>
                </table>

                <div style="margin-bottom:12px;font-size:15px;font-weight:800;">Order summary</div>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border-collapse:collapse;">
                    @foreach($order->items as $item)
                        <tr>
                            <td style="padding:13px 0;border-bottom:1px solid #e9eeeb;">
                                <div style="font-size:14px;font-weight:700;color:#1b2923;">{{ $item->product_name }}</div>
                                <div style="padding-top:4px;font-size:12px;color:#819088;">Qty: {{ $item->quantity }}@if($item->sku) &nbsp;&middot;&nbsp; SKU: {{ $item->sku }}@endif</div>
                                @if(!empty($item->options))
                                    <div style="padding-top:3px;font-size:12px;color:#819088;">{{ collect($item->options)->pluck('value')->filter()->join(' / ') }}</div>
                                @endif
                            </td>
                            <td align="right" style="padding:13px 0;border-bottom:1px solid #e9eeeb;font-size:14px;font-weight:700;white-space:nowrap;">&#2547;{{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </table>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:20px;font-size:13px;line-height:25px;">
                    <tr><td style="color:#728078;">Subtotal</td><td align="right">&#2547;{{ number_format((float) $order->subtotal, 2) }}</td></tr>
                    @if((float) $order->discount_amount > 0)<tr><td style="color:#28764a;">Discount</td><td align="right" style="color:#28764a;">-&#2547;{{ number_format((float) $order->discount_amount, 2) }}</td></tr>@endif
                    <tr><td style="color:#728078;">Shipping</td><td align="right">@if((float) $order->shipping_charge === 0.0) Free @else &#2547;{{ number_format((float) $order->shipping_charge, 2) }} @endif</td></tr>
                    <tr><td style="padding-top:9px;border-top:1px solid #e3e9e5;font-size:16px;font-weight:800;">Total</td><td align="right" style="padding-top:9px;border-top:1px solid #e3e9e5;font-size:18px;font-weight:800;color:#ff5941;">&#2547;{{ number_format((float) $order->grand_total, 2) }}</td></tr>
                </table>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:28px;background:#f7f9f8;border-radius:8px;">
                    <tr><td style="padding:17px 18px;">
                        <div style="font-size:13px;font-weight:800;margin-bottom:7px;">Delivery address</div>
                        <div style="font-size:13px;line-height:21px;color:#637168;">{{ $order->address->recipient_name }} &middot; {{ $order->address->phone }}<br>{{ $order->address->address_line }}@if($order->address->area), {{ $order->address->area }}@endif<br>{{ $order->address->upazila_name }}, {{ $order->address->district_name }}, {{ $order->address->division_name }}@if($order->address->postal_code) - {{ $order->address->postal_code }}@endif</div>
                    </td></tr>
                </table>

                <p style="margin:26px 0 0;font-size:12px;line-height:20px;color:#8a9690;">Payment: <strong style="color:#56645d;">{{ $order->payment_method === 'cod' ? 'Cash on delivery' : 'SSLCommerz' }}</strong>. This is an automated confirmation email.</p>
            </td></tr>
            <tr><td align="center" style="padding:22px 20px 0;font-size:11px;line-height:18px;color:#929e98;">&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
