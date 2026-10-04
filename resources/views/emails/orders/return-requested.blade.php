<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; padding: 20px; color: #333;">
    <h2>{{ __('messages.return_email_requested_heading') }}</h2>
    <p>{{ __('messages.return_email_requested_body', ['number' => $order->order_number]) }}</p>
    <p><strong>Item:</strong> {{ $item->product_name }} × {{ $item->quantity }}</p>
    @if($item->rental_start_date)
        <p><strong>Rental period:</strong> {{ $item->rental_start_date->format('M d, Y') }} → {{ $item->rental_end_date->format('M d, Y') }}</p>
    @endif
    <p><a href="{{ route('provider.orders.show', $order) }}">{{ __('messages.order_mail_view_order') }}</a></p>
    <p style="color: #666; font-size: 12px;">{{ __('messages.app_name') }}</p>
</body>
</html>