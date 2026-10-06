<!DOCTYPE html>
<html>
<body style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f3f4f6;">
    <div style="background: #dc2626; color: white; padding: 24px; border-radius: 12px 12px 0 0; text-align: center;">
        <h1 style="margin: 0; font-size: 22px;">🚨 EMERGENCY SOS ALERT</h1>
        <p style="margin: 8px 0 0; font-size: 14px; opacity: 0.95;">Immediate action required</p>
    </div>

    <div style="background: white; padding: 24px; border-radius: 0 0 12px 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="margin: 0 0 16px; color: #111827; font-size: 18px;">
            {{ $sos->traveler->name ?? 'A traveler' }} needs emergency help
        </h2>

        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 130px;"><strong>Location:</strong></td>
                <td style="padding: 8px 0; color: #111827;">{{ $sos->latitude }}, {{ $sos->longitude }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;"><strong>Google Maps:</strong></td>
                <td style="padding: 8px 0;">
                    <a href="https://maps.google.com/?q={{ $sos->latitude }},{{ $sos->longitude }}"
                       style="color: #2563eb; text-decoration: underline;">
                        Open in Maps →
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;"><strong>Time:</strong></td>
                <td style="padding: 8px 0; color: #111827;">{{ $sos->created_at->format('M d, Y H:i') }} (NPT)</td>
            </tr>
            @if($sos->booking)
            <tr>
                <td style="padding: 8px 0; color: #6b7280;"><strong>Booking ID:</strong></td>
                <td style="padding: 8px 0; color: #111827;">#{{ $sos->booking->id }}</td>
            </tr>
            @endif
            @if($sos->message)
            <tr>
                <td style="padding: 8px 0; color: #6b7280; vertical-align: top;"><strong>Message:</strong></td>
                <td style="padding: 8px 0; color: #111827;">{{ $sos->message }}</td>
            </tr>
            @endif
        </table>

        <div style="margin-top: 20px; padding: 16px; background: #fef2f2; border-left: 4px solid #dc2626; border-radius: 8px;">
            <p style="margin: 0; color: #991b1b; font-size: 14px;">
                <strong>⚠ Immediate response needed.</strong> Please contact rescue services and the traveler.
            </p>
        </div>
    </div>

    <p style="text-align: center; color: #9ca3af; font-size: 12px; margin-top: 20px;">
        TravelAI Nepal — Safety System · <a href="{{ url('/') }}" style="color: #2563eb;">travelainepal.com</a>
    </p>
</body>
</html>