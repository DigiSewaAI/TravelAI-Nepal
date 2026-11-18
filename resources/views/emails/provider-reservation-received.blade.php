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
        <h2>✅ New Table Reservation!</h2>
        <p>Hello <strong>{{ $reservation->provider->name ?? 'Provider' }}</strong>,</p>
        <p>You have a new table reservation request.</p>
        
        <div class="details">
            <p><strong>Customer Name:</strong> {{ $reservation->customer_name }}</p>
            <p><strong>Phone:</strong> {{ $reservation->customer_phone }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y') }}</p>
            <p><strong>Time:</strong> {{ $reservation->reservation_time }}</p>
            <p><strong>Guests:</strong> {{ $reservation->guests }}</p>
            @if($reservation->special_requests)
                <p><strong>Requests:</strong> {{ $reservation->special_requests }}</p>
            @endif
        </div>

        <p>Please log in to your provider dashboard to confirm or reject this reservation.</p>
        
        <a href="{{ url('/provider/restaurant/reservations') }}" class="btn">View in Dashboard</a>
        
        <p style="margin-top: 30px; font-size: 12px; color: #6b7280;">TravelAI Nepal — AI + data-driven trekking ecosystem.</p>
    </div>
</body>
</html>