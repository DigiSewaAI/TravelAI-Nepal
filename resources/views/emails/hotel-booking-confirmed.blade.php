<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f9fafb; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 30px;">
        @if(!empty($logoData))
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="{{ $logoData }}" alt="{{ $provider->name }}" style="max-height: 60px;">
            </div>
        @endif
        <h2 style="color: #059669; margin-top: 0;">✅ Your Booking is Confirmed!</h2>
        <p>Hello <strong>{{ $booking->guest_name }}</strong>,</p>
        <p>Great news! Your booking at <strong>{{ $provider->name }}</strong> has been confirmed.</p>
        <div style="background: #f0fdf4; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #059669;">
            <p style="margin: 5px 0;"><strong>Booking Number:</strong> {{ $booking->booking_number }}</p>
            <p style="margin: 5px 0;"><strong>Room:</strong> {{ $room->room_number ?? '' }} ({{ $room->room_type ?? '' }})</p>
            <p style="margin: 5px 0;"><strong>Check-in:</strong> {{ $booking->check_in_date->format('M d, Y') }}</p>
            <p style="margin: 5px 0;"><strong>Check-out:</strong> {{ $booking->check_out_date->format('M d, Y') }}</p>
            <p style="margin: 5px 0;"><strong>Nights:</strong> {{ $booking->nights }}</p>
            <p style="margin: 5px 0;"><strong>Total Amount:</strong> Rs. {{ number_format($booking->amount, 2) }}</p>
        </div>
        <p style="color: #6b7280; font-size: 13px;">We look forward to welcoming you at {{ $provider->name }}!</p>
    </div>
</body>
</html>