<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Traveler Marked Payment as Sent</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9fafb; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #2563eb; }
        .info-box { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin: 15px 0; }
        .info-row { padding: 6px 0; border-bottom: 1px solid #f3f4f6; }
        .info-row:last-child { border-bottom: none; }
        .label { color: #6b7280; font-size: 13px; display: block; }
        .value { font-weight: bold; color: #111827; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #ddd; padding-top: 20px; }
        .btn { display: inline-block; background: #2563eb; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .alert { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 4px; margin: 15px 0; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>💳 Payment Notice Received</h1>
    </div>

    <p>Hi {{ $provider->name ?? 'there' }},</p>

    <p>A traveler has marked their payment as <strong>"I've Paid"</strong> for a booking with your service. Please verify the payment on your end and confirm.</p>

    <div class="info-box">
        <div class="info-row">
            <span class="label">Booking ID</span>
            <span class="value">#{{ $booking->id }}</span>
        </div>
        <div class="info-row">
            <span class="label">Service</span>
            <span class="value">{{ $service->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Traveler</span>
            <span class="value">{{ $traveler->name ?? 'N/A' }}</span>
        </div>
        @if($traveler && $traveler->email)
        <div class="info-row">
            <span class="label">Traveler Email</span>
            <span class="value">{{ $traveler->email }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="label">Start Date</span>
            <span class="value">{{ $booking->start_date ? $booking->start_date->format('M d, Y') : 'TBD' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Notified At</span>
            <span class="value">{{ $booking->payment_notice_sent_at ? $booking->payment_notice_sent_at->format('M d, Y H:i') : 'N/A' }}</span>
        </div>
    </div>

    <div class="alert">
        <strong>Note:</strong> TravelAI does not process this payment. Settlement is direct between you and the traveler.
    </div>

    <div style="text-align: center; margin: 20px 0;">
        <a href="{{ route('provider.bookings.show', $booking->id) }}" class="btn">View Booking</a>
    </div>

    <div class="footer">
        <p>This is an automated message from TravelAI Nepal. Please do not reply to this email.</p>
        <p>© {{ date('Y') }} TravelAI Nepal — AI + data-driven trekking ecosystem.</p>
    </div>
</div>
</body>
</html>