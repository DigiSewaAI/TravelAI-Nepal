<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; padding: 20px; background: #f8fafc; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">

        {{-- Header with logo + brand --}}
        <div style="background: #2563eb; padding: 25px; text-align: center;">
            @if(!empty($brand['logo_base64']))
                <img src="cid:provider-logo"
                     alt="{{ $brand['name'] ?? 'TravelAI Nepal' }}"
                     style="max-height: 70px; max-width: 180px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto;">
            @endif
            <h1 style="color: white; margin: 0; font-size: 22px; font-weight: bold;">
                {{ $brand['name'] ?? 'TravelAI Nepal' }}
            </h1>
        </div>

        {{-- Body --}}
        <div style="padding: 30px;">
            <p style="color: #1e293b; font-size: 16px; margin: 0 0 15px;">
                {{ __('messages.email_invoice_greeting', ['name' => $traveler->name ?? 'Traveler']) }}
            </p>

            <p style="color: #475569; font-size: 14px; line-height: 1.6; margin: 0 0 20px;">
                {{ __('messages.email_invoice_thanks') }}
            </p>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background: #f8fafc; border-radius: 8px;">
                <tr>
                    <td style="padding: 12px 16px; color: #64748b; font-size: 13px;">{{ __('messages.invoice_number') }}</td>
                    <td style="padding: 12px 16px; color: #1e293b; font-size: 13px; text-align: right; font-weight: 600;">
                        {{ $booking->booking_number ?? '#' . $booking->id }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0;">{{ __('messages.service') }}</td>
                    <td style="padding: 12px 16px; color: #1e293b; font-size: 13px; text-align: right; font-weight: 600; border-top: 1px solid #e2e8f0;">
                        {{ $booking->service->name ?? 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 16px; color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0;">{{ __('messages.start_date') }}</td>
                    <td style="padding: 12px 16px; color: #1e293b; font-size: 13px; text-align: right; font-weight: 600; border-top: 1px solid #e2e8f0;">
                        {{ $booking->start_date ? $booking->start_date->format('M d, Y') : 'N/A' }}
                    </td>
                </tr>
            </table>

            <p style="color: #1e293b; font-size: 14px; margin: 20px 0 0; font-weight: 600;">
                📎 {{ __('messages.email_invoice_attached') }}
            </p>

            <p style="color: #64748b; font-size: 13px; margin: 20px 0 0;">
                {{ __('messages.email_invoice_questions') }}
            </p>
        </div>

        {{-- Provider footer --}}
        <div style="background: #f1f5f9; padding: 20px; text-align: center; border-top: 1px solid #e2e8f0;">
            <p style="color: #1e293b; font-size: 14px; font-weight: 600; margin: 0 0 5px;">
                {{ $provider->name ?? 'Provider' }}
            </p>
            @if($provider->address)
                <p style="color: #64748b; font-size: 12px; margin: 2px 0;">{{ $provider->address }}</p>
            @endif
            @if($provider->contact_email || $provider->contact_phone)
                <p style="color: #64748b; font-size: 12px; margin: 2px 0;">
                    {{ $provider->contact_email ?? '' }}@if($provider->contact_email && $provider->contact_phone) &middot; @endif{{ $provider->contact_phone ?? '' }}
                </p>
            @endif

            @if(($brand['tier'] ?? 'travelai') === 'travelai' || ($brand['tier'] ?? '') === 'logo_only')
                <p style="color: #94a3b8; font-size: 11px; margin: 15px 0 0; border-top: 1px solid #e2e8f0; padding-top: 12px;">
                    Powered by TravelAI Nepal
                </p>
            @endif
        </div>
    </div>
</body>
</html>