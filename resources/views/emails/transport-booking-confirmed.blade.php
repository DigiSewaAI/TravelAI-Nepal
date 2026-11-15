<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background: #ecfdf5;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff;">

        {{-- Header Bar --}}
        <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 28px 30px; text-align: center;">
            @if($logoData)
                <img src="{{ $logoData }}" alt="{{ $provider->name }}" style="max-height: 60px; margin-bottom: 12px; display: inline-block;">
            @elseif($logoUrl ?? null)
                <img src="{{ $logoUrl }}" alt="{{ $provider->name }}" style="max-height: 60px; margin-bottom: 12px; display: inline-block;">
            @endif
            <h1 style="color: #ffffff; font-size: 22px; margin: 0; font-weight: 700;">{{ $provider->name }}</h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 13px; margin: 6px 0 0;">Booking Confirmed</p>
        </div>

        {{-- Body --}}
        <div style="padding: 30px;">

            <h2 style="color: #059669; margin: 0 0 8px; font-size: 20px;">✅ Your Booking is Confirmed!</h2>
            <p style="color: #4b5563; font-size: 15px; margin: 8px 0 20px;">Hello <strong>{{ $booking->passenger_name }}</strong>,</p>
            <p style="color: #4b5563; font-size: 15px; margin: 0 0 20px;">Great news! Your transport booking with <strong>{{ $provider->name }}</strong> has been confirmed.</p>

            {{-- Booking Ref Box --}}
            <div style="background: #059669; color: #fff; padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; text-align: center;">
                <div style="font-size: 11px; opacity: 0.85; text-transform: uppercase; letter-spacing: 1px;">Booking Reference</div>
                <div style="font-size: 20px; font-weight: 800; margin-top: 4px;">{{ $booking->booking_ref }}</div>
            </div>

            {{-- Details --}}
            <div style="background: #ecfdf5; border-left: 4px solid #059669; padding: 18px; border-radius: 8px; margin-bottom: 20px;">
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
        @if($booking->trip_id)
        <tr>
            <td style="padding: 6px 0; color: #64748b;">{{ __('messages.passengers_count') }}</td>
            <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $booking->passenger_count }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #64748b;">{{ __('messages.booking_type_label') }}</td>
            <td style="padding: 6px 0; text-align: right; font-weight: 600;">
                @if($booking->booking_type === 'full_vehicle')
                    {{ __('messages.full_vehicle_label') }}
                @else
                    {{ __('messages.per_seat_label') }}
                @endif
            </td>
        </tr>
        @endif
        @if($booking->pickup_point)
        <tr>
            <td style="padding: 6px 0; color: #64748b;">{{ __('messages.pickup_label') }}</td>
            <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $booking->pickup_point }}</td>
        </tr>
        @endif
        <tr>
            <td style="padding: 6px 0; color: #64748b;">Amount</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #059669;">
                            @if((float)$booking->amount > 0)
                                Rs {{ number_format((float) $booking->amount, 2) }}
                            @else
                                {{ __('messages.price_on_request') ?? 'Price on request' }}
                            @endif
                        </td>
                    </tr>
                    </table>
</div>

@if($booking->trip_id && $booking->price_per_unit && $booking->total_amount)
<div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;">
    <div style="font-size: 12px; color: #065f46; font-weight: 800; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">{{ __('messages.price_breakdown') }}</div>
    <div style="font-size: 15px; color: #111827; font-weight: 600;">
        {{ $booking->passenger_count }} × Rs {{ number_format($booking->price_per_unit, 2) }} = <strong>Rs {{ number_format($booking->total_amount, 2) }}</strong>
    </div>
</div>
@endif

{{-- Reminder --}}
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px;">
                <p style="margin: 0; color: #166534; font-size: 13px;"><strong>🚀 Ready to go!</strong> Please arrive at the departure point 15 minutes early. Bring your booking reference.</p>
            </div>

            {{-- User Journey CTA --}}
            @if($isRegistered ?? false)
            <div style="background: #ecfdf5; border: 1px solid #bbf7d0; padding: 16px 18px; border-radius: 8px; margin: 20px 0; text-align: center;">
                <p style="margin: 0 0 10px; color: #166534; font-size: 13px;"><strong>Booking is in your account</strong> — view details, download receipts, and manage this trip from your dashboard.</p>
                <a href="{{ $dashboardUrl ?? url('/traveler/dashboard-v2') }}" style="display: inline-block; background: #059669; color: #ffffff; padding: 11px 22px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px;">
                    Open My Dashboard →
                </a>
            </div>
            @else
            <div style="background: #ecfdf5; border: 1px solid #bbf7d0; padding: 16px 18px; border-radius: 8px; margin: 20px 0; text-align: center;">
                <p style="margin: 0 0 10px; color: #166534; font-size: 13px;"><strong>Create an account</strong> to keep track of this booking and get updates on future trips.</p>
                <a href="{{ $registerUrl ?? url('/register') }}" style="display: inline-block; background: #059669; color: #ffffff; padding: 11px 22px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px;">
                    Register Now →
                </a>
            </div>
            @endif

            <p style="color: #6b7280; font-size: 13px; margin: 0;">We look forward to serving you!</p>
        </div>

        {{-- Footer --}}
        <div style="background: #f8fafc; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0;">
            @if($provider->contact_phone)
                <p style="margin: 4px 0; color: #64748b; font-size: 12px;">📞 {{ $provider->contact_phone }}</p>
            @endif
            @if($provider->contact_email)
                <p style="margin: 4px 0; color: #64748b; font-size: 12px;">✉️ {{ $provider->contact_email }}</p>
            @endif
            <p style="margin: 10px 0 0; color: #94a3b8; font-size: 11px;">Need to cancel? Contact us at {{ $provider->contact_phone ?? $provider->contact_email ?? 'the operator' }}.</p>
            <p style="margin: 10px 0 0; color: #94a3b8; font-size: 11px;">© {{ date('Y') }} {{ $provider->name }}</p>
        </div>
    </div>
</body>
</html>