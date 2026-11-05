@extends('layouts.admin')

@section('title', __('messages.admin_restaurant_orders_title'))

@section('content')
<div style="padding: 20px;">
    <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 20px;">
        {{ __('messages.admin_restaurant_orders_title') }}
    </h1>

    <form method="GET" style="margin-bottom: 20px; display: flex; gap: 8px;">
        <input type="text" name="search" value="{{ $search }}"
               placeholder="{{ __('messages.admin_restaurant_search_placeholder') }}"
               style="flex: 1; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
        <button type="submit" style="padding: 10px 20px; background: #2563eb; color: #fff; border: 0; border-radius: 8px; font-weight: 600;">
            {{ __('messages.admin_restaurant_search_btn') }}
        </button>
    </form>

    <div style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f8fafc;">
                <tr>
                    <th style="padding: 12px; text-align: left; font-size: 12px; text-transform: uppercase; color: #64748b;">{{ __('messages.admin_restaurant_order_number') }}</th>
                    <th style="padding: 12px; text-align: left; font-size: 12px; text-transform: uppercase; color: #64748b;">{{ __('messages.admin_restaurant_customer') }}</th>
                    <th style="padding: 12px; text-align: left; font-size: 12px; text-transform: uppercase; color: #64748b;">{{ __('messages.admin_restaurant_receipt_status') }}</th>
                    <th style="padding: 12px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr style="border-top: 1px solid #f1f5f9;">
                        <td style="padding: 12px; font-weight: 600;">{{ $order->order_number }}</td>
                        <td style="padding: 12px; font-size: 13px; color: #475569;">
                            {{ $order->customer_name ?? '—' }}
                            @if($order->customer_email)<br><span style="font-size: 11px; color: #94a3b8;">{{ $order->customer_email }}</span>@endif
                        </td>
                        <td style="padding: 12px; font-size: 12px;">
                            @if($order->receipt_code_used_at)
                                <span style="background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 6px; font-weight: 600;">{{ __('messages.admin_restaurant_status_used') }}</span>
                            @elseif($order->receipt_code_issued_at)
                                <span style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 6px; font-weight: 600;">{{ __('messages.admin_restaurant_status_issued') }} ({{ $order->receipt_code_reissue_count }})</span>
                            @else
                                <span style="background: #f1f5f9; color: #64748b; padding: 4px 8px; border-radius: 6px; font-weight: 600;">{{ __('messages.admin_restaurant_status_none') }}</span>
                            @endif
                        </td>
                        <td style="padding: 12px; text-align: right;">
                            @if(!$order->receipt_code_used_at)
                                <a href="{{ route('admin.restaurant.orders.reissue.form', $order) }}"
                                   style="padding: 6px 12px; background: #f59e0b; color: #fff; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">
                                    {{ __('messages.admin_restaurant_reissue_btn') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="padding: 40px; text-align: center; color: #94a3b8;">—</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">{{ $orders->links() }}</div>
</div>
@endsection