<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f9fafb; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="color: #f97316; margin-top: 0;">Reservation Request Received</h2>
        <p style="color: #4b5563; font-size: 15px;">Hello <strong>{{ $reservation->customer_name }}</strong>,</p>
        <p style="color: #4b5563; font-size: 15px;">Your table reservation request at <strong>{{ $provider->name }}</strong> has been received. The restaurant will confirm it shortly.</p>
        
        <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 5px 0; color: #111827;"><strong>Date:</strong> {{ $reservation->reservation_date->format('M d, Y') }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Time:</strong> {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Guests:</strong> {{ $reservation->guests }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Table:</strong> {{ $reservation->table->table_number ?? 'Any Available' }}</p>
        </div>
        
        <p style="color: #6b7280; font-size: 13px; margin-top: 30px;">Thank you for choosing {{ $provider->name }}!</p>
    </div>
</body>
</html>