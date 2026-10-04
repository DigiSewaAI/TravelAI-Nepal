<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; padding: 20px; color: #333;">
    <h2>{{ __('messages.order_mail_placed_heading') }}</h2>
    <p>{{ __('messages.order_mail_placed_body', ['number' => $order->order_number]) }}</p>
    <p><strong>{{ __('messages.order_total') }}:</strong> {{ number_format($order->total, 2) }} {{ $order->currency }}</p>
    <p><strong>{{ __('messages.checkout_contact_name') }}:</strong> {{ $order->contact_name }}</p>
    <p><a href="{{ route('provider.orders.show', $order) }}">{{ __('messages.order_mail_view_order') }}</a></p>
    <p style="color: #666; font-size: 12px;">{{ __('messages.app_name') }}</p>
</body>
</html>