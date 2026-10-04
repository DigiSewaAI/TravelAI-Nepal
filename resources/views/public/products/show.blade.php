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

            {{-- PATH-3C C3: stock display for shop products --}}
            @if($product->isShop() && $product->shopDetail?->stock_count !== null)
                <div class="mt-4">
                    @if($product->shopDetail->stock_count > 0)
                        <span class="text-green-600 font-medium text-sm">
                            <i class="fas fa-check-circle"></i>
                            {{ __('messages.product_in_stock', ['count' => $product->shopDetail->stock_count]) }}
                        </span>
                    @else
                        <span class="text-red-600 font-medium text-sm">
                            <i class="fas fa-times-circle"></i>
                            {{ __('messages.product_out_of_stock') }}
                        </span>
                    @endif
                </div>
            @endif

            @if($product->isShop() && $product->shopDetail?->stock_count === 0)
                <div class="mt-6">
                    <button type="button" disabled
                            class="w-full bg-gray-300 text-gray-600 py-3 rounded-lg font-semibold cursor-not-allowed">
                        {{ __('messages.product_out_of_stock') }}
                    </button>
                </div>
            @else
            <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-6">
                @csrf

                @if($product->product_type === 'rental')
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" name="rental_start_date" required
                                   min="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" name="rental_end_date" required
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                @endif

                <div class="flex gap-3 items-center">
                    <input type="number" name="quantity" value="1" min="1" max="100"
                           class="w-20 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
                        <i class="fas fa-cart-plus mr-2"></i> {{ __('messages.cart_add_btn') }}
                    </button>
                </div>

                @if(session('error'))
                    <p class="mt-2 text-sm text-red-600">{{ session('error') }}</p>
                @endif
                @if(session('success'))
                    <p class="mt-2 text-sm text-green-600">{{ session('success') }}</p>
                @endif
            </form>
            @endif

            @if($product->product_type === 'wholesale' && auth()->check())
                <a href="{{ route('wholesale.rfq.create', $product) }}"
                   class="block mt-3 text-center bg-amber-600 hover:bg-amber-700 text-white py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-file-invoice mr-2"></i> {{ __('messages.rfq_request_quote_btn') }}
                </a>
            @endif

        </div>
    </div>

</section>

@endsection