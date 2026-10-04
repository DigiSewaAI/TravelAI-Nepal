<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; padding: 20px; color: #333;">
    <h2>{{ __('messages.email_return_confirmed_heading') }}</h2>
    <p>{{ __('messages.email_return_confirmed_body', ['number' => $order->order_number]) }}</p>
    <p><strong>Item:</strong> {{ $item->product_name }} × {{ $item->quantity }}</p>
    <p><strong>Condition:</strong> {{ ucfirst($item->return_condition) }}</p>

    <table style="width:100%; border-collapse: collapse; margin-top: 12px;">
        <tr>
            <td style="padding: 6px 0;">{{ __('messages.return_deposit_paid') }}</td>
            <td style="text-align: right;">{{ number_format($item->rental_deposit * $item->quantity, 2) }} NPR</td>
        </tr>
        @if($item->return_condition === 'damaged')
            <tr>
                <td style="padding: 6px 0; color: #d97706;">{{ __('messages.return_damage_deduction') }}</td>
                <td style="text-align: right; color: #d97706;">- {{ number_format($item->rental_deposit * $item->quantity - $refundAmount, 2) }} NPR</td>
            </tr>
        @elseif($item->return_condition === 'lost')
            <tr>
                <td style="padding: 6px 0; color: #dc2626;">{{ __('messages.return_lost_deduction') }}</td>
                <td style="text-align: right; color: #dc2626;">- {{ number_format($item->rental_deposit * $item->quantity, 2) }} NPR</td>
            </tr>
        @endif
        @php $lateFee = $item->calculateLateFee(); @endphp
        @if($lateFee > 0)
            <tr>
                <td style="padding: 6px 0; color: #d97706;">{{ __('messages.return_late_fee') }}</td>
                <td style="text-align: right; color: #d97706;">- {{ number_format($lateFee, 2) }} NPR</td>
            </tr>
        @endif
        <tr style="font-weight: bold; border-top: 1px solid #ccc;">
            <td style="padding: 8px 0;">{{ __('messages.return_net_refund') }}</td>
            <td style="text-align: right;">{{ number_format($refundAmount, 2) }} NPR</td>
        </tr>
    </table>

    <p style="margin-top: 16px;"><a href="{{ route('orders.show', $order) }}">{{ __('messages.order_mail_view_order') }}</a></p>
    <p style="color: #666; font-size: 12px;">{{ __('messages.app_name') }}</p>
</body>
</html>