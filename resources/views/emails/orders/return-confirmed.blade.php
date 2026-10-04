<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; padding: 20px; color: #333;">
    <h2>{{ __('messages.return_email_confirmed_heading') }}</h2>
    <p>{{ __('messages.return_email_confirmed_body', ['number' => $order->order_number]) }}</p>
    <p><strong>Item:</strong> {{ $item->product_name }} × {{ $item->quantity }}</p>
    <p><strong>Condition:</strong> {{ ucfirst($item->return_condition) }}</p>
    <p><strong>Deposit refund:</strong> NPR {{ number_format($refundAmount, 2) }}</p>
    <p><a href="{{ route('orders.show', $order) }}">{{ __('messages.order_mail_view_order') }}</a></p>
    <p style="color: #666; font-size: 12px;">{{ __('messages.app_name') }}</p>
</body>
</html>