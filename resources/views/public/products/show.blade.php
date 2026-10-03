@extends('layouts.public')

@section('title', $product->name . ' | ' . __('messages.app_name'))
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 150))

@section('content')

@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
    $baseCurrency = $product->currency ?? 'NPR';
    $displayPrice = $currencyService->convert((float) $product->price, $baseCurrency, $displayCurrency);
    $formattedPrice = $currencyService->format($displayPrice, $displayCurrency);
@endphp

<section class="max-w-6xl mx-auto px-6 md:px-10 py-8">

    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ url('/') }}" class="hover:text-blue-600">{{ __('messages.home') }}</a>
        <span class="mx-2">/</span>
        @if($product->product_type === 'shop')
            <a href="{{ route('public.shop.index') }}" class="hover:text-blue-600">{{ __('messages.nav_shop') }}</a>
        @elseif($product->product_type === 'rental')
            <a href="{{ route('public.rental.index') }}" class="hover:text-blue-600">{{ __('messages.nav_rental') }}</a>
        @else
            <a href="{{ route('public.wholesale.index') }}" class="hover:text-blue-600">{{ __('messages.nav_wholesale') }}</a>
        @endif
        <span class="mx-2">/</span>
        <span>{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <div class="rounded-xl overflow-hidden bg-gray-100">
            @if($product->cover_image)
                <img src="{{ asset('storage/' . $product->cover_image) }}" alt="{{ $product->name }}"
                     class="w-full h-96 object-cover">
            @else
                <div class="w-full h-96 flex items-center justify-center text-gray-400">
                    <i class="fas fa-image text-6xl"></i>
                </div>
            @endif
        </div>

        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>

            @if($product->provider)
                <p class="text-sm text-gray-500 mt-2">{{ __('messages.products_detail_provider') }}: {{ $product->provider->name }}</p>
            @endif

            <div class="mt-6">
                <span class="text-3xl font-bold text-blue-600">{{ $formattedPrice }}</span>
                @if($product->product_type === 'rental')
                    <span class="text-sm text-gray-500">{{ __('messages.products_per_day') }}</span>
                    @if($product->rentalDetail && $product->rentalDetail->rental_deposit)
                        <p class="text-sm text-gray-600 mt-1">
                            {{ __('messages.products_deposit_label') }}: {{ $currencyService->format((float)$product->rentalDetail->rental_deposit, $baseCurrency) }}
                        </p>
                    @endif
                @endif

                @if($product->product_type === 'wholesale' && $product->wholesaleDetail)
                    <p class="text-sm text-gray-600 mt-1">
                        {{ __('messages.products_min_order') }}: {{ $product->wholesaleDetail->min_order_qty }}
                    </p>
                    <p class="text-xs text-gray-500">{{ __('messages.products_bulk_available') }}</p>
                @endif
            </div>

            @if($product->description)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('messages.products_detail_about') }}</h3>
                    <p class="text-gray-700 whitespace-pre-line">{{ $product->description }}</p>
                </div>
            @endif

            <div class="mt-6 bg-gray-50 rounded-xl p-4">
                <p class="text-sm text-gray-500">{{ __('messages.products_add_cart_coming') }}</p>
            </div>

        </div>
    </div>

</section>

@endsection