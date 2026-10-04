<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; padding: 20px; color: #333;">
    <h2>{{ __('messages.order_mail_payment_verified_heading') }}</h2>
    <p>{{ __('messages.order_mail_payment_verified_body', ['number' => $order->order_number]) }}</p>
    <p><a href="{{ route('orders.show', $order) }}">{{ __('messages.order_mail_view_order') }}</a></p>
    <p style="color: #666; font-size: 12px;">{{ __('messages.app_name') }}</p>
</body>
</html>