<!DOCTYPE html>
<html>
<body style="font-family:Arial,sans-serif;background:#f5f6fb;padding:24px;margin:0">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;padding:32px;border:1px solid #e7eaf3">

<h2 style="color:#111827;margin:0 0 16px;font-size:22px">TravelAI Nepal</h2>

<div style="background:#eef0fe;border-left:4px solid #6366f1;padding:14px;border-radius:8px;margin-bottom:20px">
    <p style="margin:0;color:#4f46e5;font-weight:700;font-size:14px">RFQ: {{ $rfq->rfq_number }}</p>
</div>

@if($type === 'submitted')
    <p><strong>{{ $rfq->buyer->name ?? 'A buyer' }}</strong> has submitted a new RFQ for your product.</p>
@endif

@if($type === 'quoted')
    <p><strong>{{ $rfq->provider->name ?? 'Provider' }}</strong> has provided a quote for your RFQ.</p>
    @if($rfq->provider_response)
        <div style="background:#f9fafb;border:1px solid #e5e7eb;padding:14px;border-radius:8px;margin:14px 0;font-size:13px;color:#374151;white-space:pre-line;line-height:1.6">{{ $rfq->provider_response }}</div>
    @endif
@endif

@if($type === 'accepted')
    <p><strong>{{ $rfq->buyer->name ?? 'Buyer' }}</strong> has accepted your quote.</p>
@endif

@if($type === 'rejected')
    <p><strong>{{ $rfq->buyer->name ?? 'Buyer' }}</strong> has rejected your quote.</p>
@endif

@if($type === 'message')
    <p>A new message has been posted on this RFQ.</p>
    @if(isset($messageText) && $messageText)
        <div style="background:#f9fafb;border:1px solid #e5e7eb;padding:14px;border-radius:8px;margin:14px 0;font-size:13px;color:#374151;white-space:pre-line;line-height:1.6">{{ $messageText }}</div>
    @endif
@endif

<table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:13px">
    <tr>
        <td style="padding:8px 0;color:#6b7280">Product:</td>
        <td style="padding:8px 0;font-weight:700">{{ $rfq->product->name ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td style="padding:8px 0;color:#6b7280">Quantity:</td>
        <td style="padding:8px 0;font-weight:700">{{ $rfq->quantity }}</td>
    </tr>
    @if($rfq->quoted_price)
        <tr>
            <td style="padding:8px 0;color:#6b7280">Unit Price:</td>
            <td style="padding:8px 0;font-weight:700">NPR {{ number_format($rfq->quoted_price, 2) }}</td>
        </tr>
        <tr>
            <td style="padding:8px 0;color:#6b7280">Total:</td>
            <td style="padding:8px 0;font-weight:700">NPR {{ number_format($rfq->quoted_total, 2) }}</td>
        </tr>
    @endif
    @if($rfq->valid_until)
        <tr>
            <td style="padding:8px 0;color:#6b7280">Valid Until:</td>
            <td style="padding:8px 0;font-weight:700">{{ $rfq->valid_until->format('M d, Y') }}</td>
        </tr>
    @endif
</table>

<p style="margin-top:24px;font-size:12px;color:#6b7280">— TravelAI Nepal</p>

</div>
</body>
</html>