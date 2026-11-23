<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f9fafb; padding: 20px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 30px;">
        <h2 style="color: #059669; margin-top: 0; font-size: 20px;">✅ Great News — Quote Accepted!</h2>

        <p style="color: #4b5563; font-size: 14px;">Hello <strong>{{ $provider->name }}</strong>,</p>
        <p style="color: #4b5563; font-size: 14px;">
            <strong>{{ $order->client_name }}</strong> has accepted your quotation. You can start production now.
        </p>

        <div style="background: #ecfdf5; border-left: 4px solid #059669; padding: 16px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 4px 0; font-size: 13px;"><strong>Order:</strong> {{ $order->booking_number }}</p>
            <p style="margin: 4px 0; font-size: 13px;"><strong>Customer:</strong> {{ $order->client_name }} ({{ $order->client_email }})</p>
            <p style="margin: 4px 0; font-size: 13px;"><strong>Accepted Quote:</strong> #{{ $quote->version }}</p>
            <p style="margin: 4px 0; font-size: 13px;"><strong>Amount:</strong> {{ $quote->currency }} {{ number_format($quote->quoted_amount, 2) }}</p>
            <p style="margin: 4px 0; font-size: 13px;"><strong>Production:</strong> {{ $quote->production_days }} days</p>
        </div>

        <p style="color: #4b5563; font-size: 14px;">Log in to your dashboard to update the order status to <strong>In Production</strong> when you begin work.</p>

        <div style="text-align: center; margin: 28px 0;">
            <a href="{{ url('/provider/art-craft/custom-orders/' . $order->id) }}" style="display: inline-block; background: #059669; color: #fff; padding: 14px 32px; border-radius: 10px; text-decoration: none; font-weight: 800; font-size: 14px;">
                View Order →
            </a>
        </div>
    </div>
</body>
</html>