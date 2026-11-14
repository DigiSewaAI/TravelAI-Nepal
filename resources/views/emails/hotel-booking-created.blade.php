<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f9fafb; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="color: #0ea5e9; margin-top: 0;">Booking Confirmed 🎉</h2>
        <p style="color: #4b5563; font-size: 15px;">Hello <strong>{{ $booking->guest_name }}</strong>,</p>
        <p style="color: #4b5563; font-size: 15px;">Your booking at <strong>{{ $provider->name }}</strong> has been confirmed.</p>
        
        <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0ea5e9;">
            <p style="margin: 5px 0; color: #111827;"><strong>Booking Number:</strong> {{ $booking->booking_number }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Room:</strong> {{ $room->room_number }} ({{ $room->room_type }})</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Check-in:</strong> {{ $booking->check_in_date->format('M d, Y') }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Check-out:</strong> {{ $booking->check_out_date->format('M d, Y') }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Nights:</strong> {{ $booking->nights }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Total Amount:</strong> Rs. {{ number_format($booking->amount, 2) }}</p>
        </div>
        
        <p style="color: #6b7280; font-size: 13px; margin-top: 30px;">Thank you for choosing {{ $provider->name }}!</p>
    </div>
</body>
</html>