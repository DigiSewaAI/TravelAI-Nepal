<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f9fafb; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="color: #10b981; margin-top: 0;">✅ Your Reservation is Confirmed!</h2>
        <p style="color: #4b5563; font-size: 15px;">Hello <strong>{{ $reservation->customer_name }}</strong>,</p>
        <p style="color: #4b5563; font-size: 15px;">Great news! Your table reservation at <strong>{{ $provider->name }}</strong> has been confirmed.</p>
        
        <div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 5px 0; color: #111827;"><strong>Date:</strong> {{ $reservation->reservation_date->format('M d, Y') }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Time:</strong> {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Guests:</strong> {{ $reservation->guests }}</p>
            <p style="margin: 5px 0; color: #111827;"><strong>Table:</strong> {{ $reservation->table->table_number ?? 'Any Available' }}</p>
        </div>
        
        <p style="color: #6b7280; font-size: 14px;">We look forward to seeing you at {{ $provider->name }}!</p>
        <p style="color: #9ca3af; font-size: 12px; margin-top: 30px;">If you need to cancel, please contact the restaurant at {{ $provider->contact_phone ?? $provider->contact_email ?? 'the restaurant' }}.</p>
    </div>
</body>
</html>