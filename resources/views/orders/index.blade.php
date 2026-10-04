@extends('layouts.public')

@section('title', __('messages.orders_title') . ' | ' . __('messages.app_name'))

@section('content')
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
@endphp

<section class="max-w-5xl mx-auto px-6 md:px-10 py-8">

    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('messages.orders_title') }}</h1>

    @if($orders->isEmpty())
        <div class="text-center py-16 bg-gray-50 rounded-xl">
            <i class="fas fa-receipt text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">{{ __('messages.orders_empty') }}</p>
            <a href="{{ route('public.shop.index') }}" class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm">
                {{ __('messages.cart_continue_shopping') }}
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($orders as $order)
                <a href="{{ route('orders.show', $order) }}"
                   class="block bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $order->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $order->items->count() }} items</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold
                                @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                                @elseif($order->status === 'delivered') bg-green-100 text-green-800
                                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                            <p class="font-bold text-blue-600 mt-2">
                                {{ $currencyService->format(
                                    $currencyService->convert((float) $order->total, $order->currency, $displayCurrency),
                                    $displayCurrency
                                ) }}
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    @endif

</section>
@endsection