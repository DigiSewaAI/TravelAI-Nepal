<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background:#f5f3ff;">
    <div style="max-width:600px;margin:0 auto;background:#fff;">
        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#7c3aed,#5b21b6);padding:28px 30px;text-align:center;">
            @if($logoData)
                <img src="{{ $logoData }}" alt="{{ $provider->name }}" style="max-height:60px;margin-bottom:12px;display:inline-block;">
            @elseif($logoUrl ?? null)
                <img src="{{ $logoUrl }}" alt="{{ $provider->name }}" style="max-height:60px;margin-bottom:12px;display:inline-block;">
            @endif
            <h1 style="color:#fff;font-size:22px;margin:0;font-weight:700;">{{ $provider->name }}</h1>
            <p style="color:rgba(255,255,255,0.9);font-size:13px;margin:6px 0 0;">Handmade Art & Commissions</p>
        </div>

        {{-- Body --}}
        <div style="padding:30px;">
            <h2 style="color:#7c3aed;margin:0 0 8px;font-size:20px;">
                @if($isCustom) 🎨 Custom Commission Request Received
                @else ✉️ Enquiry Received
                @endif
            </h2>
            <p style="color:#4b5563;font-size:15px;margin:8px 0 20px;">Hello <strong>{{ $order->client_name }}</strong>,</p>
            <p style="color:#4b5563;font-size:15px;margin:0 0 20px;">
                @if($isCustom)
                    Your custom commission request has been received. The artist will review it and send you a formal quote shortly.
                @else
                    Your enquiry about <strong>{{ $order->artwork_name_snapshot ?? 'the artwork' }}</strong> has been received. The artist will respond shortly.
                @endif
            </p>

            {{-- Reference Box --}}
            <div style="background:#7c3aed;color:#fff;padding:14px 20px;border-radius:10px;margin-bottom:20px;text-align:center;">
                <div style="font-size:11px;opacity:0.85;text-transform:uppercase;letter-spacing:1px;">Reference</div>
                <div style="font-size:20px;font-weight:800;margin-top:4px;">{{ $order->booking_number }}</div>
            </div>

            {{-- Details --}}
            <div style="background:#f5f3ff;border-left:4px solid #7c3aed;padding:18px;border-radius:8px;margin-bottom:20px;">
                <table style="width:100%;font-size:14px;color:#111827;border-collapse:collapse;">
                    @if($isCustom)
                        <tr>
                            <td style="padding:6px 0;color:#64748b;">Type</td>
                            <td style="padding:6px 0;text-align:right;font-weight:600;">{{ $order->type }}</td>
                        </tr>
                        @if($order->quantity > 1)
                        <tr>
                            <td style="padding:6px 0;color:#64748b;">Quantity</td>
                            <td style="padding:6px 0;text-align:right;font-weight:600;">{{ $order->quantity }}</td>
                        </tr>
                        @endif
                        @if($order->materials)
                        <tr>
                            <td style="padding:6px 0;color:#64748b;">Materials</td>
                            <td style="padding:6px 0;text-align:right;font-weight:600;">{{ $order->materials }}</td>
                        </tr>
                        @endif
                        @if($order->dimensions)
                        <tr>
                            <td style="padding:6px 0;color:#64748b;">Dimensions</td>
                            <td style="padding:6px 0;text-align:right;font-weight:600;">{{ $order->dimensions }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding:6px 0;color:#64748b;">Budget</td>
                            <td style="padding:6px 0;text-align:right;font-weight:700;color:#7c3aed;">Rs {{ number_format((float)$order->budget, 2) }}</td>
                        </tr>
                        @if($order->deadline)
                        <tr>
                            <td style="padding:6px 0;color:#64748b;">Deadline</td>
                            <td style="padding:6px 0;text-align:right;font-weight:600;">{{ $order->deadline->format('M d, Y') }}</td>
                        </tr>
                        @endif
                    @else
                        <tr>
                            <td style="padding:6px 0;color:#64748b;">Artwork</td>
                            <td style="padding:6px 0;text-align:right;font-weight:600;">{{ $order->artwork_name_snapshot }}</td>
                        </tr>
                        <tr>
                            <td style="padding:6px 0;color:#64748b;">Price</td>
                            <td style="padding:6px 0;text-align:right;font-weight:700;color:#7c3aed;">Rs {{ number_format((float)$order->artwork_price_snapshot, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            {{-- Next Steps --}}
            <div style="background:#fffbeb;border:1px solid #fde68a;padding:14px 18px;border-radius:8px;margin-bottom:20px;">
                <p style="margin:0;color:#92400e;font-size:13px;">
                    @if($isCustom)
                        <strong>📌 What's next?</strong> The artist will review your request and send a quote with price, materials, and timeline. You can then accept or request revisions.
                    @else
                        <strong>📌 What's next?</strong> The artist will confirm availability and contact you about reservation and payment.
                    @endif
                </p>
            </div>

            <p style="color:#6b7280;font-size:13px;margin:0;">Thank you for choosing {{ $provider->name }}!</p>
        </div>

        {{-- Footer --}}
        <div style="background:#f8fafc;padding:20px 30px;text-align:center;border-top:1px solid #e2e8f0;">
            @if($provider->contact_phone)
                <p style="margin:4px 0;color:#64748b;font-size:12px;">📞 {{ $provider->contact_phone }}</p>
            @endif
            @if($provider->contact_email)
                <p style="margin:4px 0;color:#64748b;font-size:12px;">✉️ {{ $provider->contact_email }}</p>
            @endif
            <p style="margin:10px 0 0;color:#94a3b8;font-size:11px;">© {{ date('Y') }} {{ $provider->name }}</p>
        </div>
    </div>
</body>
</html>