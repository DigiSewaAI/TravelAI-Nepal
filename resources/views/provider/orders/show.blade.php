@extends('layouts.provider')

@section('title', $order->order_number)
@section('header', $order->order_number)

@section('content')
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
    $provider = auth()->user()->ownProvider();
@endphp

<div class="bg-white rounded-xl shadow-sm border p-6">

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 border border-green-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Order Header --}}
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $order->created_at->format('M d, Y H:i') }}</p>
        </div>
        <span class="px-3 py-1.5 rounded-full text-sm font-semibold
            @if($order->status === 'pending') bg-yellow-100 text-yellow-800
            @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
            @elseif($order->status === 'delivered') bg-green-100 text-green-800
            @else bg-gray-100 text-gray-800
            @endif">
            {{ ucfirst($order->status) }}
        </span>
    </div>

    {{-- Customer --}}
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <h3 class="font-semibold text-gray-900 mb-2">{{ __('messages.provider_order_customer') }}</h3>
        <p class="text-sm text-gray-700">{{ $order->contact_name }}</p>
        <p class="text-sm text-gray-500">{{ $order->contact_email }}</p>
        @if($order->contact_phone)
            <p class="text-sm text-gray-500">{{ $order->contact_phone }}</p>
        @endif
        @if($order->shipping_address)
            <p class="text-sm text-gray-500 mt-2">
                {{ $order->shipping_address }}{{ $order->shipping_city ? ', ' . $order->shipping_city : '' }}
            </p>
        @endif
    </div>

    {{-- Your Items (only) --}}
    <h3 class="font-semibold text-gray-900 mb-3">{{ __('messages.provider_order_your_items') }}</h3>
    <div class="space-y-3 mb-6">
        @foreach($items as $item)
            <div class="flex justify-between items-center bg-gray-50 rounded-lg p-4">
                <div>
                    <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                    <p class="text-xs text-gray-500">
                        {{ ucfirst($item->product_type) }} · Qty: {{ $item->quantity }}
                        @if($item->rental_days) · {{ $item->rental_days }} days @endif
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Status: <span class="font-semibold">{{ ucfirst($item->provider_status) }}</span>
                    </p>

                    {{-- PATH-3C C2: Return confirmation for rental items --}}
                    @if($item->product_type === 'rental')
                        @if($item->isReturnConfirmed())
                            <div class="mt-2 p-2 bg-green-50 rounded text-xs">
                                <p class="text-green-800 font-medium">
                                    {{ __('messages.return_confirmed') }} — {{ ucfirst($item->return_condition) }}
                                </p>
                                @if($item->deposit_refund_amount !== null)
                                    <p class="text-green-700">
                                        {{ __('messages.return_refund_amount') }}: NPR {{ number_format($item->deposit_refund_amount, 2) }}
                                    </p>
                                @endif
                            </div>
                        @elseif($item->hasReturnRequest())
                            <div class="mt-3 p-3 bg-yellow-50 rounded border border-yellow-200">
                                <p class="text-xs font-semibold text-yellow-800 mb-2">
                                    {{ __('messages.provider_return_confirm_title') }}
                                </p>
                                <form method="POST" action="{{ route('provider.orders.items.confirm-return', [$order, $item]) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="return_condition" required class="w-full border rounded px-2 py-1 text-xs">
                                        <option value="good">{{ __('messages.return_condition_good') }}</option>
                                        <option value="damaged">{{ __('messages.return_condition_damaged') }}</option>
                                        <option value="lost">{{ __('messages.return_condition_lost') }}</option>
                                    </select>
                                    <textarea name="return_notes" rows="2" maxlength="1000"
                                              placeholder="{{ __('messages.return_notes_label') }}"
                                              class="w-full border rounded px-2 py-1 text-xs"></textarea>
                                    <button type="submit" class="text-xs bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded">
                                        {{ __('messages.provider_return_confirm_btn') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endif
                </div>
                <span class="font-bold text-blue-600">
                    {{ $currencyService->format(
                        $currencyService->convert((float) $item->line_total, $order->currency, $displayCurrency),
                        $displayCurrency
                    ) }}
                </span>
            </div>
        @endforeach
    </div>

    {{-- PATH-3B B7: Timeline --}}
    <div class="border-t pt-4 mb-4">
        @include('partials.order-timeline', ['order' => $order])
    </div>

    {{-- PATH-3C C1: Deposit tracking --}}
    @if($order->deposit_total > 0)
        <div class="bg-blue-50 rounded-lg p-4 mb-4 border border-blue-200">
            <h3 class="font-semibold text-blue-900 mb-2 text-sm">{{ __('messages.provider_order_deposit') }}</h3>
            <div class="text-sm space-y-1">
                <div class="flex justify-between">
                    <span class="text-gray-700">{{ __('messages.order_deposit_total') }}</span>
                    <span class="font-mono">
                        {{ $currencyService->format($currencyService->convert((float)$order->deposit_total, $order->currency, $displayCurrency), $displayCurrency) }}
                    </span>
                </div>
                @if($order->deposit_refunded_amount > 0)
                    <div class="flex justify-between text-green-700">
                        <span>{{ __('messages.order_deposit_refunded') }}</span>
                        <span class="font-mono">
                            {{ $currencyService->format($currencyService->convert((float)$order->deposit_refunded_amount, $order->currency, $displayCurrency), $displayCurrency) }}
                        </span>
                    </div>
                @endif
                <div class="flex justify-between pt-2 border-t border-blue-200 font-semibold">
                    <span>{{ __('messages.order_deposit_held') }}</span>
                    <span class="font-mono text-blue-700">
                        {{ $currencyService->format($currencyService->convert($order->deposit_held, $order->currency, $displayCurrency), $displayCurrency) }}
                    </span>
                </div>
            </div>
        </div>
    @endif

    {{-- PATH-3B B5: Payment Verification --}}
    @if($order->isPaymentNotified() && !$order->isPaymentVerified())
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded mb-4">
            <p class="font-medium text-yellow-800 mb-2">
                {{ __('messages.provider_order_payment_notified') }}
            </p>
            <div class="text-sm text-gray-700 mb-2">
                <div><strong>{{ __('messages.order_payment_reference') }}:</strong>
                    {{ $order->payment_reference }}</div>
                @if($order->payment_note)
                    <div><strong>{{ __('messages.order_payment_note') }}:</strong>
                        {{ $order->payment_note }}</div>
                @endif
                <div class="text-xs text-gray-500">
                    {{ $order->payment_notice_sent_at->diffForHumans() }}
                </div>
            </div>
            <form method="POST" action="{{ route('provider.orders.verifyPayment', $order) }}">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-check-circle mr-1"></i>
                    {{ __('messages.provider_order_verify_payment') }}
                </button>
            </form>
        </div>
    @elseif($order->isPaymentVerified())
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded mb-4">
            <p class="text-green-800">
                <i class="fas fa-check-circle"></i>
                {{ __('messages.order_payment_verified') }}
            </p>
        </div>
    @endif

    {{-- Status Update Form --}}
    @php
        $currentStatus = $order->getProviderStatusForProvider($provider->id);
    @endphp
    <div class="border-t pt-4">
        <h3 class="font-semibold text-gray-900 mb-3">{{ __('messages.provider_order_update_status') }}</h3>
        <form method="POST" action="{{ route('provider.orders.updateStatus', $order) }}" class="flex gap-3 items-end">
            @csrf
            @method('PATCH')
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.provider_order_status_label') }}</label>
                <select name="status" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="confirmed" {{ $currentStatus === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="shipped"   {{ $currentStatus === 'shipped'   ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ $currentStatus === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ $currentStatus === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium">
                Update
            </button>
        </form>
    </div>

    <div class="mt-6 text-sm">
        <a href="{{ route('provider.orders.index') }}" class="text-gray-500 hover:text-blue-600">
            ← {{ __('messages.provider_orders_title') }}
        </a>
    </div>

</div>
@endsection