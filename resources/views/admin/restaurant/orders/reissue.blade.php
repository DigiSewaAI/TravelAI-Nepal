@extends('layouts.admin')

@section('title', __('messages.admin_restaurant_reissue_title'))

@section('content')
<div style="padding: 20px; max-width: 640px;">
    <h1 style="font-size: 22px; font-weight: 800; margin-bottom: 20px;">
        {{ __('messages.admin_restaurant_reissue_title') }}
    </h1>

    <div style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; margin-bottom: 16px;">
        <p style="margin-bottom: 8px;"><strong>{{ __('messages.admin_restaurant_order_number') }}:</strong> {{ $order->order_number }}</p>
        <p style="margin-bottom: 8px;"><strong>{{ __('messages.admin_restaurant_customer') }}:</strong> {{ $order->customer_name ?? '—' }} @if($order->customer_email) ({{ $order->customer_email }}) @endif</p>
        <p style="margin-bottom: 0;"><strong>{{ __('messages.admin_restaurant_receipt_status') }}:</strong>
            @if($order->receipt_code_used_at)
                {{ __('messages.admin_restaurant_status_used') }}
            @elseif($order->receipt_code_issued_at)
                {{ __('messages.admin_restaurant_status_issued') }} ({{ $order->receipt_code_reissue_count }} reissues)
            @else
                {{ __('messages.admin_restaurant_status_none') }}
            @endif
        </p>
    </div>

    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
            {{ session('error') }}
        </div>
    @endif

    @if($claimed)
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px;">
            {{ __('messages.admin_restaurant_reissue_claimed') }}
        </div>
    @else
        <div style="background: #fef3c7; border: 1px solid #fcd34d; color: #78350f; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
            ⚠️ {{ __('messages.admin_restaurant_reissue_warning') }}
        </div>

        <form method="POST" action="{{ route('admin.restaurant.orders.reissue', $order) }}">
            @csrf
            <button type="submit" style="padding: 12px 24px; background: #dc2626; color: #fff; border: 0; border-radius: 8px; font-weight: 700; cursor: pointer;">
                {{ __('messages.admin_restaurant_reissue_confirm') }}
            </button>
        </form>
    @endif

    <a href="{{ route('admin.restaurant.orders.index') }}" style="display: inline-block; margin-top: 16px; color: #2563eb; text-decoration: none; font-size: 13px;">
        ← {{ __('messages.admin_restaurant_orders_title') }}
    </a>
</div>
@endsection