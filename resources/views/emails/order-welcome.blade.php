<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">

    <div style="background: linear-gradient(135deg, #059669, #047857); padding: 30px 20px; text-align: center;">
        <h1 style="color: #fff; margin: 0; font-size: 24px;">
            {{ $isRegistered ? __('messages.email_order_welcome_heading_registered') : __('messages.email_order_welcome_heading_new') }}
        </h1>
        <p style="color: #d1fae5; margin: 8px 0 0 0; font-size: 14px;">{{ $brand['name'] ?? __('messages.app_name') }}</p>
    </div>

    <div style="padding: 30px 25px;">
        <p style="font-size: 15px; color: #333; margin: 0 0 20px 0;">
            {{ __('messages.email_invoice_greeting', ['name' => $order->contact_name ?? $user->name ?? 'Traveler']) }}
        </p>
        <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 20px 0;">
            {{ __('messages.email_order_welcome_body') }}
        </p>

        <div style="background: #d1fae5; border: 2px solid #059669; border-radius: 12px; padding: 20px; margin: 20px 0;">
            <div style="font-size: 12px; color: #065f46; text-transform: uppercase; font-weight: bold; margin-bottom: 8px;">
                {{ __('messages.email_order_welcome_order_id') }}
            </div>
            <div style="font-size: 24px; font-weight: bold; color: #065f46; letter-spacing: 1px;">
                {{ $order->order_number ?? '#' . $order->id }}
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">{{ __('messages.email_order_welcome_items') }}</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ $itemsCount }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">{{ __('messages.email_order_welcome_total') }}</td>
                <td style="padding: 8px 0; color: #059669; font-weight: bold; text-align: right; font-size: 15px;">
                    {{ $displayTotal }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 13px;">{{ __('messages.status') }}</td>
                <td style="padding: 8px 0; color: #111; font-weight: bold; text-align: right; font-size: 13px;">{{ ucfirst($order->status) }}</td>
            </tr>
        </table>

        @if(in_array($order->status, ['pending', 'processing'], true))
            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 15px; margin: 15px 0; border-radius: 4px;">
                <p style="font-size: 13px; color: #92400e; margin: 0; line-height: 1.5;">
                    ⏳ <strong>{{ __('messages.email_order_welcome_pending_label') }}</strong>
                    {{ __('messages.email_order_welcome_pending_text') }}
                </p>
            </div>
        @endif

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;">

        @if($isRegistered)
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 15px 0;">
                ✅ {{ __('messages.email_order_welcome_registered_note') }}
            </p>
            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ route('orders.show', $order) }}"
                   style="display: inline-block; background: #059669; color: #fff; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px;">
                    {{ __('messages.email_order_welcome_view_cta') }}
                </a>
            </div>
        @else
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 12px 0;">
                📝 {{ __('messages.email_order_welcome_new_note') }}
            </p>
            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 15px 0;">
                {{ __('messages.email_order_welcome_register_intro') }}
            </p>
            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ $registerUrl }}"
                   style="display: inline-block; background: #2563eb; color: #fff; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px;">
                    {{ __('messages.email_order_welcome_register_cta') }}
                </a>
            </div>
            <p style="font-size: 12px; color: #9ca3af; text-align: center; margin: 15px 0 0 0;">
                {{ __('messages.email_booking_welcome_register_email', ['email' => $order->contact_email ?? '—']) }}
            </p>
        @endif
    </div>

    <div style="background: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
        <p style="font-size: 11px; color: #9ca3af; margin: 0;">© {{ date('Y') }} {{ $brand['name'] ?? __('messages.app_name') }} · All rights reserved.</p>
    </div>
</div>
</body>
</html>