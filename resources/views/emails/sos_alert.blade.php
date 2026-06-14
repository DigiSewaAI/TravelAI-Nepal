<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SOS Alert</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .alert-box { background-color: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; }
        .button { background-color: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block; }
        hr { margin: 20px 0; }
    </style>
</head>
<body>
<div class="container">
    <h2 style="color: #dc2626;">🚨 SOS ALERT</h2>
    <p>Dear <strong>{{ $agency_name }}</strong>,</p>
    <p>A trekker has triggered an emergency SOS.</p>

    <div class="alert-box">
        <p><strong>🧑 Trekker:</strong> {{ $trekker_name }}</p>
        <p><strong>🏔️ Trek:</strong> {{ $trek_name }}</p>
        <p><strong>📝 Message:</strong> {{ $message }}</p>
        <p><strong>📍 Location:</strong><br>
            Latitude: {{ $latitude }}<br>
            Longitude: {{ $longitude }}<br>
            <a href="{{ $google_maps_link }}" target="_blank" class="button">View on Google Maps</a>
        </p>
        <p><strong>⏰ Time:</strong> {{ $alert_time }}</p>
    </div>

    <p><strong>Immediate action required.</strong> Please contact the trekker or coordinate with local rescue.</p>
    <hr>
    <p style="font-size: 12px; color: #666;">This is an automated message from TravelAI Nepal.</p>
</div>
</body>
</html>