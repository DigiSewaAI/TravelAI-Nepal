<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Confirmed</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1f2937;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f4f5;padding:24px 12px;">
  <tr>
    <td align="center">
      <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.06);">

        {{-- Brand header --}}
        <tr>
          <td style="background:linear-gradient(135deg,#f97316,#ea580c);padding:28px 24px;text-align:center;">
            @if(!empty($brand['logo_base64'] ?? null))
              {{-- Queue-safe Inline Image Embedding --}}
              <img src="{{ $message->embedData(base64_decode($brand['logo_base64']), 'brand-logo.png', $brand['logo_mime'] ?? 'image/png') }}" alt="{{ $brand['name'] ?? 'Logo' }}" style="max-height:56px;max-width:180px;display:block;margin:0 auto 12px;">
            @endif
            <h1 style="margin:0;font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-0.3px;">{{ $brand['name'] ?? $provider->name ?? 'TravelAI Nepal' }}</h1>
            <p style="margin:6px 0 0;font-size:13px;color:rgba(255,255,255,0.85);">{{ $provider->address ?? 'Kathmandu, Nepal' }}</p>
          </td>
        </tr>

        {{-- Order confirmation --}}
        <tr>
          <td style="padding:28px 24px 12px;">
            <h2 style="margin:0 0 8px;font-size:20px;font-weight:800;color:#111827;">Order Confirmed 🎉</h2>
            <p style="margin:0 0 16px;font-size:14px;color:#6b7280;line-height:1.5;">
              Thank you for your order, {{ $order->customer_name ?? 'Guest' }}. We've received it and will start preparing it shortly.
            </p>
          </td>
        </tr>

        {{-- Order meta --}}
        <tr>
          <td style="padding:0 24px;">
            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f9fafb;border-radius:12px;padding:14px 16px;margin-bottom:16px;">
              <tr>
                <td style="font-size:11px;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;padding:0 0 4px;">Order Number</td>
                <td align="right" style="font-size:11px;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;padding:0 0 4px;">Date</td>
              </tr>
              <tr>
                <td style="font-size:15px;font-weight:800;color:#111827;padding:0;">{{ $order->order_number }}</td>
                <td align="right" style="font-size:13px;font-weight:600;color:#111827;padding:0;">{{ $order->created_at->format('M d, Y · h:i A') }}</td>
              </tr>
            </table>
          </td>
        </tr>

        {{-- Claim code box --}}
        @if($plainCode)
        <tr>
          <td style="padding:0 24px 16px;">
            <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#fef3c7;border:2px dashed #f59e0b;border-radius:12px;padding:20px;">
              <tr>
                <td align="center">
                  <p style="margin:0 0 8px;font-size:11px;color:#92400e;font-weight:800;text-transform:uppercase;letter-spacing:1px;">Your Claim Code</p>
                  <p style="margin:0 0 12px;font-size:28px;font-weight:900;color:#7c2d12;letter-spacing:4px;font-family:'Courier New',monospace;">{{ $plainCode }}</p>
                  <a href="{{ route('traveler.restaurant.claim.form') }}" style="display:inline-block;background:#f59e0b;color:#ffffff;text-decoration:none;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:700;">Claim Your Order →</a>
                  <p style="margin:10px 0 0;font-size:11px;color:#92400e;line-height:1.5;">Keep this code safe. It's shown only once. Use it to claim this order on TravelAI Nepal.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        @endif

        {{-- Items --}}
        <tr>
          <td style="padding:0 24px;">
            <h3 style="margin:0 0 12px;font-size:14px;font-weight:800;color:#111827;">Order Items</h3>
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
              <tr style="border-bottom:1px solid #e5e7eb;">
                <th align="left" style="padding:8px 0;font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;">Item</th>
                <th align="center" style="padding:8px 0;font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;">Qty</th>
                <th align="right" style="padding:8px 0;font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;">Total</th>
              </tr>
              @foreach($order->items as $item)
              <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:10px 0;font-size:13px;color:#111827;font-weight:600;">{{ $item->item_name }}</td>
                <td align="center" style="padding:10px 0;font-size:13px;color:#6b7280;">{{ $item->quantity }}</td>
                <td align="right" style="padding:10px 0;font-size:13px;color:#111827;font-weight:700;">{{ $order->currency ?? 'NPR' }} {{ number_format($item->line_total, 2) }}</td>
              </tr>
              @endforeach
              <tr>
                <td colspan="2" align="right" style="padding:14px 0 4px;font-size:14px;font-weight:800;color:#111827;">Total</td>
                <td align="right" style="padding:14px 0 4px;font-size:16px;font-weight:800;color:#059669;">{{ $order->currency ?? 'NPR' }} {{ number_format($order->total, 2) }}</td>
              </tr>
            </table>
          </td>
        </tr>

        <tr><td style="height:24px;"></td></tr>

        {{-- Footer --}}
        <tr>
          <td style="background:#f9fafb;padding:20px 24px;text-align:center;border-top:1px solid #e5e7eb;">
            <p style="margin:0 0 6px;font-size:12px;color:#6b7280;">Questions about your order? Contact us at {{ $provider->contact_email ?? $provider->contact_phone ?? 'the restaurant' }}.</p>
            <p style="margin:0;font-size:11px;color:#9ca3af;">{{ $brand['copyright'] ?? ('© ' . date('Y') . ' ' . ($brand['name'] ?? 'TravelAI Nepal') . '. All rights reserved.') }}</p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>