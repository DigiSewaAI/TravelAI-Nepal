<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">

    <div style="background: linear-gradient(135deg, #6366f1, #4f46e5); padding: 30px 20px; text-align: center;">
        <h1 style="color: #fff; margin: 0; font-size: 24px;">
            {{ $isRegistered ? __('messages.email_booking_welcome_heading_registered') : __('messages.email_booking_welcome_heading_new') }}
        </h1>
        <p style="color: #e0e7ff; margin: 8px 0 0 0; font-size: 14px;">{{ $brand['name'] ?? __('messages.app_name') }}</p>
    </div>

    <div style="padding: 30px 25px;">
        <p style="font-size: 15px; color: #333; margin: 0 0 20px 0;">
            {{ __('messages.email_invoice_greeting', ['name' => $traveler->name ?? 'Traveler']) }}
        </p>
        <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 20px 0;">
            {{ __('messages.email_booking_welcome_body', ['provider' => $provider->name ?? '—']) }}
        </p>

        <div style="background: #e0e7ff; border: 2px solid #6366f1; border-radius: 12px; padding: 20px; margin: 20px 0;">
            <div style="font-size: 12px; color: #3730a3; text-transform: uppercase; font-weight: bold; margin-bottom: 8px;">
                {{ __('messages.email_booking_welcome_booking_id') }}
            </div>
            <div style="font-size: 24px; font-weight: bold; color: #3730a3; letter-spacing: 1px;">
                {{ $booking->booking_number ?? '#' . $booking->id }}
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">{{ __('messages.service') }}</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ $service?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">{{ __('messages.email_booking_welcome_provider') }}</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ $provider?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">{{ __('messages.start_date') }}</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">{{ __('messages.email_booking_welcome_guests') }}</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ $booking->guest_count ?? 1 }}</td>
            </tr>
                        <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">{{ __('messages.status') }}</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ ucfirst($booking->status) }}</td>
            </tr>
        </table>

        @if($booking->status === 'pending')
            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 15px; margin: 15px 0; border-radius: 4px;">
                <p style="font-size: 13px; color: #92400e; margin: 0; line-height: 1.5;">
                    ⏳ <strong>{{ __('messages.email_booking_welcome_pending_label') }}</strong>
                    {{ __('messages.email_booking_welcome_pending_text') }}
                </p>
            </div>
        @endif

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;">

        @if($isRegistered)
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 15px 0;">
                ✅ {{ __('messages.email_booking_welcome_registered_note') }}
            </p>
            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ route('traveler.bookings.show', $booking->id) }}"
                   style="display: inline-block; background: #6366f1; color: #fff; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px;">
                    {{ __('messages.email_booking_welcome_view_cta') }}
                </a>
            </div>
        @else
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 12px 0;">
                📝 {{ __('messages.email_booking_welcome_new_note') }}
            </p>
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 15px 0;">
                {{ __('messages.email_booking_welcome_register_intro') }}
            </p>
            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ $registerUrl }}"
                   style="display: inline-block; background: #2563eb; color: #fff; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px;">
                    {{ __('messages.email_booking_welcome_register_cta') }}
                </a>
            </div>
            <p style="font-size: 12px; color: #9ca3af; text-align: center; margin: 15px 0 0 0;">
                {{ __('messages.email_booking_welcome_register_email', ['email' => $traveler->email ?? '—']) }}
            </p>
        @endif

        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px 15px; margin: 25px 0; border-radius: 4px;">
            <p style="font-size: 13px; color: #1e40af; margin: 0; line-height: 1.5;">
                <strong>{{ __('messages.email_booking_welcome_why_label') }}</strong> {{ __('messages.email_booking_welcome_why_text') }}
            </p>
        </div>
    </div>

    <div style="background: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
        <p style="font-size: 11px; color: #9ca3af; margin: 0;">© {{ date('Y') }} {{ $brand['name'] ?? __('messages.app_name') }} · All rights reserved.</p>
    </div>
</div>
</body>
</html>