<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f9fafb; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="color: #10b981; margin-top: 0;">💰 Payment Notice Received</h2>
        <p style="color: #4b5563; font-size: 15px;">Hello <strong>Provider</strong>,</p>
        <p style="color: #4b5563; font-size: 15px;">The traveler has submitted a payment notice for Order <strong>#{{ $order->order_number }}</strong>.</p>
        
        <div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 5px 0; color: #111827;"><strong>Payment Reference:</strong> {{ $paymentReference }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Total Amount:</strong> {{ $order->currency ?? 'NPR' }} {{ number_format($order->total, 2) }}</p>
        </div>
        
        <p style="color: #6b7280; font-size: 14px;">Please log in to your dashboard to verify this payment.</p>
        <a href="{{ url('/provider/orders/' . $order->id) }}" style="display:inline-block; background:#3b82f6; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px; margin-top:10px;">View Order</a>
    </div>
</body>
</html>