<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background: #f0f9ff;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff;">

        {{-- Header Bar (colored, not blank) --}}
        <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); padding: 28px 30px; text-align: center;">
            @if($logoData)
                <img src="{{ $logoData }}" alt="{{ $provider->name }}" style="max-height: 60px; margin-bottom: 12px; display: inline-block;">
            @elseif($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $provider->name }}" style="max-height: 60px; margin-bottom: 12px; display: inline-block;">
            @endif
            <h1 style="color: #ffffff; font-size: 22px; margin: 0; font-weight: 700;">{{ $provider->name }}</h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 13px; margin: 6px 0 0;">Transport Booking</p>
        </div>

        {{-- Body --}}
        <div style="padding: 30px;">

            <h2 style="color: #0284c7; margin: 0 0 8px; font-size: 20px;">🚌 Booking Request Received</h2>
            <p style="color: #4b5563; font-size: 15px; margin: 8px 0 20px;">Hello <strong>{{ $booking->passenger_name }}</strong>,</p>
            <p style="color: #4b5563; font-size: 15px; margin: 0 0 20px;">Your transport booking request with <strong>{{ $provider->name }}</strong> has been received. The operator will confirm shortly.</p>

            {{-- Booking Ref Box --}}
            <div style="background: #0284c7; color: #fff; padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; text-align: center;">
                <div style="font-size: 11px; opacity: 0.85; text-transform: uppercase; letter-spacing: 1px;">Booking Reference</div>
                <div style="font-size: 20px; font-weight: 800; margin-top: 4px;">{{ $booking->booking_ref }}</div>
            </div>

            {{-- Details --}}
            <div style="background: #f0f9ff; border-left: 4px solid #0284c7; padding: 18px; border-radius: 8px; margin-bottom: 20px;">
                <table style="width: 100%; font-size: 14px; color: #111827; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Route</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $booking->route_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Vehicle</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $vehicle->vehicle_type ?? 'Vehicle' }} — {{ $vehicle->vehicle_number ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Date</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $booking->travel_date->format('M d, Y') }}</td>
                    </tr>
                    @if($booking->travel_time)
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Time</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $booking->travel_time }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Amount</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #0284c7;">
                            @if((float)$booking->amount > 0)
                                Rs {{ number_format((float) $booking->amount, 2) }}
                            @else
                                {{ __('messages.price_on_request') ?? 'Price on request' }}
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            {{-- CTA / Next Steps --}}
            <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;">
                <p style="margin: 0; color: #92400e; font-size: 13px;"><strong>📌 What's next?</strong> The operator will review and confirm your booking. You'll receive a confirmation email once it's approved.</p>
            </div>

            {{-- User Journey CTA --}}
            @if($isRegistered ?? false)
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 16px 18px; border-radius: 8px; margin: 20px 0; text-align: center;">
                <p style="margin: 0 0 10px; color: #1e40af; font-size: 13px;"><strong>Track your booking</strong> — get real-time updates, manage trips, and view booking details from your dashboard.</p>
                <a href="{{ $dashboardUrl ?? url('/traveler/dashboard-v2') }}" style="display: inline-block; background: #0284c7; color: #ffffff; padding: 11px 22px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px;">
                    View My Dashboard →
                </a>
            </div>
            @else
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 16px 18px; border-radius: 8px; margin: 20px 0; text-align: center;">
                <p style="margin: 0 0 10px; color: #1e40af; font-size: 13px;"><strong>Create an account</strong> to track your booking, get real-time updates, and manage future trips easily.</p>
                <a href="{{ $registerUrl ?? url('/register') }}" style="display: inline-block; background: #0284c7; color: #ffffff; padding: 11px 22px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px;">
                    Register Now →
                </a>
            </div>
            @endif

            <p style="color: #6b7280; font-size: 13px; margin: 0;">Thank you for choosing {{ $provider->name }}!</p>
        </div>

        {{-- Footer --}}
        <div style="background: #f8fafc; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0;">
            @if($provider->contact_phone)
                <p style="margin: 4px 0; color: #64748b; font-size: 12px;">📞 {{ $provider->contact_phone }}</p>
            @endif
            @if($provider->contact_email)
                <p style="margin: 4px 0; color: #64748b; font-size: 12px;">✉️ {{ $provider->contact_email }}</p>
            @endif
            <p style="margin: 10px 0 0; color: #94a3b8; font-size: 11px;">© {{ date('Y') }} {{ $provider->name }}</p>
        </div>
    </div>
</body>
</html>