<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 20px; }
        .card { background: #ffffff; padding: 30px; border-radius: 10px; max-width: 600px; margin: auto; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .details { background: #f9fafb; padding: 15px; border-radius: 5px; margin-top: 20px; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="card">
        <h2>✅ New Booking Received!</h2>
        <p>Hello <strong>{{ $booking->service->provider->name ?? 'Provider' }}</strong>,</p>
        <p>You have a new booking for <strong>{{ $booking->service->name }}</strong>.</p>
        
        <div class="details">
            <p><strong>Booking ID:</strong> #{{ $booking->id }}</p>
            <p><strong>Traveler Name:</strong> {{ $booking->traveler->name ?? 'Guest' }}</p>
            <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') }}</p>
            <p><strong>Guests:</strong> {{ $booking->guest_count }}</p>
        </div>

        <p>Please log in to your provider dashboard to view the full details and confirm the booking.</p>
        
        <a href="{{ url('/provider/bookings/' . $booking->id) }}" class="btn">View Booking in Dashboard</a>
        
        <p style="margin-top: 30px; font-size: 12px; color: #6b7280;">TravelAI Nepal — AI + data-driven trekking ecosystem.</p>
    </div>
</body>
</html>