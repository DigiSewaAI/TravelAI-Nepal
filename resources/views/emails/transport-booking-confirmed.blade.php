<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f9fafb; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        @if($logoData)
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{{ $logoData }}" alt="{{ $provider->name }}" style="max-height: 60px;">
        </div>
        @endif

        <h2 style="color: #10b981; margin-top: 0;">✅ Your Booking is Confirmed!</h2>
        <p style="color: #4b5563; font-size: 15px;">Hello <strong>{{ $booking->passenger_name }}</strong>,</p>
        <p style="color: #4b5563; font-size: 15px;">Great news! Your transport booking with <strong>{{ $provider->name }}</strong> has been confirmed.</p>

        <div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 5px 0; color: #111827;"><strong>Booking Ref:</strong> {{ $booking->booking_ref }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Route:</strong> {{ $booking->route_name }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Vehicle:</strong> {{ $vehicle->vehicle_type ?? 'Vehicle' }} — {{ $vehicle->vehicle_number ?? '' }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Date:</strong> {{ $booking->travel_date->format('M d, Y') }}</p>
            @if($booking->travel_time)
            <p style="margin: 5px 0; color: #111827;"><strong>Time:</strong> {{ $booking->travel_time }}</p>
            @endif
            <p style="margin: 5px 0; color: #111827;"><strong>Amount:</strong> Rs {{ number_format((float) $booking->amount, 2) }}</p>
        </div>

        <p style="color: #6b7280; font-size: 14px;">We look forward to serving you. Please arrive at the departure point 15 minutes early.</p>
        <p style="color: #9ca3af; font-size: 12px; margin-top: 30px;">If you need to cancel, please contact the operator at {{ $provider->contact_phone ?? $provider->contact_email ?? 'the operator' }}.</p>
    </div>
</body>
</html>