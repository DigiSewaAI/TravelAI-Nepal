@extends('layouts.public')

@section('title', $order->order_number . ' | ' . __('messages.app_name'))

@section('content')
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
@endphp

<section class="max-w-4xl mx-auto px-6 md:px-10 py-8">

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 border border-green-300 rounded-lg">{{ session('success') }}</div>
    @endif

    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('orders.index') }}" class="hover:text-blue-600">{{ __('messages.orders_title') }}</a>
        <span class="mx-2">/</span>
        <span>{{ $order->order_number }}</span>
    </nav>

    <div class="bg-white rounded-xl shadow-sm border p-6">

        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $order->created_at->format('M d, Y H:i') }}</p>
            </div>
            <span class="inline-block px-3 py-1.5 rounded-full text-sm font-semibold
                @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                @elseif($order->status === 'delivered') bg-green-100 text-green-800
                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst($order->status) }}
            </span>
        </div>

        {{-- Items --}}
        <div class="border-t pt-4">
            <h2 class="font-semibold text-gray-900 mb-3">Items</h2>
            <div class="space-y-3">
                @foreach($order->items as $item)
                    <div class="flex justify-between items-center bg-gray-50 rounded-lg p-3">
                        <div>
                            <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ ucfirst($item->product_type) }} · Qty: {{ $item->quantity }}
                                @if($item->rental_days) · {{ $item->rental_days }} days @endif
                            </p>
                            @if($item->provider)
                                <p class="text-xs text-gray-500">by {{ $item->provider->name }}</p>
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
        </div>

        {{-- Contact + Shipping --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 border-t pt-4">
            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Contact</h3>
                <p class="text-sm text-gray-700">{{ $order->contact_name }}</p>
                <p class="text-sm text-gray-500">{{ $order->contact_email }}</p>
                @if($order->contact_phone)
                    <p class="text-sm text-gray-500">{{ $order->contact_phone }}</p>
                @endif
            </div>
            @if($order->shipping_address || $order->shipping_city)
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Shipping</h3>
                    @if($order->shipping_address)
                        <p class="text-sm text-gray-700">{{ $order->shipping_address }}</p>
                    @endif
                    <p class="text-sm text-gray-500">
                        {{ $order->shipping_city }}{{ $order->shipping_city && $order->shipping_country ? ', ' : '' }}{{ $order->shipping_country }}
                    </p>
                </div>
            @endif
        </div>

        {{-- Totals --}}
        <div class="border-t mt-6 pt-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">{{ __('messages.cart_subtotal') }}</span>
                <span>{{ $currencyService->format($currencyService->convert((float)$order->subtotal, $order->currency, $displayCurrency), $displayCurrency) }}</span>
            </div>
            @if($order->shipping_fee > 0)
                <div class="flex justify-between text-sm mt-1">
                    <span class="text-gray-600">Shipping</span>
                    <span>{{ $currencyService->format($currencyService->convert((float)$order->shipping_fee, $order->currency, $displayCurrency), $displayCurrency) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-lg font-bold mt-3 pt-3 border-t">
                <span>Total</span>
                <span class="text-blue-600">
                    {{ $currencyService->format($currencyService->convert((float)$order->total, $order->currency, $displayCurrency), $displayCurrency) }}
                </span>
            </div>
        </div>

        @if($order->notes)
            <div class="border-t mt-6 pt-4">
                <h3 class="font-semibold text-gray-900 mb-2">Notes</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $order->notes }}</p>
            </div>
        @endif

    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('orders.index') }}" class="text-sm text-gray-500 hover:text-blue-600">← {{ __('messages.orders_title') }}</a>
    </div>

</section>
@endsection