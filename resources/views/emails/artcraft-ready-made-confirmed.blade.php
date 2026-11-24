<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f9fafb; padding: 20px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 30px;">
        <div style="text-align: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb;">
            @if($provider->logo_url)
                <img src="{{ Storage::url($provider->logo_url) }}" alt="{{ $provider->name }}" style="max-height: 60px; margin-bottom: 8px;">
            @endif
            <h1 style="font-size: 20px; color: #111; margin: 0;">{{ $provider->name }}</h1>
        </div>

        <h2 style="color: #059669; margin-top: 0; font-size: 20px;">✓ Your Reservation is Confirmed</h2>

        <p style="color: #4b5563; font-size: 14px;">Hello <strong>{{ $order->client_name }}</strong>,</p>
        <p style="color: #4b5563; font-size: 14px;">
            Great news! <strong>{{ $provider->name }}</strong> has confirmed that <strong>{{ $order->artwork_name_snapshot ?? 'the artwork' }}</strong> is available for you.
        </p>

        <div style="background: #ecfdf5; border-left: 4px solid #059669; padding: 16px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 4px 0; font-size: 13px;"><strong>Order:</strong> {{ $order->booking_number }}</p>
            <p style="margin: 4px 0; font-size: 13px;"><strong>Artwork:</strong> {{ $order->artwork_name_snapshot ?? '—' }}</p>
            <p style="margin: 4px 0; font-size: 13px;"><strong>Price:</strong> {{ $order->artwork_currency_snapshot ?? 'NPR' }} {{ number_format($order->artwork_price_snapshot ?? $order->budget, 2) }}</p>
        </div>

        <p style="color: #4b5563; font-size: 14px;">The provider will contact you shortly to arrange pickup or delivery. You can also track the order status below.</p>

        <div style="text-align: center; margin: 28px 0;">
            <a href="{{ $statusUrl }}" style="display: inline-block; background: #059669; color: #fff; padding: 14px 32px; border-radius: 10px; text-decoration: none; font-weight: 800; font-size: 14px;">
                View Order Status →
            </a>
        </div>

        <p style="color: #6b7280; font-size: 12px; text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            Questions? Contact {{ $provider->contact_phone ?? $provider->contact_email ?? $provider->name }}
        </p>
    </div>
</body>
</html>