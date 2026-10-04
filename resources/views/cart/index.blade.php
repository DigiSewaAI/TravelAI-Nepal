@extends('layouts.public')

@section('title', __('messages.cart_title') . ' | ' . __('messages.app_name'))

@section('content')
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
@endphp

<section class="max-w-5xl mx-auto px-6 md:px-10 py-8">

    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('messages.cart_title') }}</h1>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 border border-green-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-800 border border-red-300 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if($items->isEmpty())
        <div class="text-center py-16 bg-gray-50 rounded-xl">
            <i class="fas fa-shopping-cart text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">{{ __('messages.cart_empty') }}</p>
            <a href="{{ route('public.shop.index') }}" class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm">
                {{ __('messages.cart_continue_shopping') }}
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-4">
                @foreach($items as $item)
                    @php
                        $product = $item->product;
                        $baseCurrency = $product->currency ?? 'NPR';
                        $unitPrice = $currencyService->convert((float) $product->price, $baseCurrency, $displayCurrency);
                        $lineTotal = $currencyService->convert($item->subtotal, $baseCurrency, $displayCurrency);
                    @endphp

                    <div class="bg-white rounded-xl shadow-sm border p-4 flex gap-4">
                        <div class="w-24 h-24 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden">
                            @if($product->cover_image)
                                <img src="{{ asset('storage/' . $product->cover_image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1">
                            <a href="{{ route('public.products.show', $product->slug) }}" class="font-semibold text-gray-900 hover:text-blue-600">
                                {{ $product->name }}
                            </a>
                            <p class="text-xs text-gray-500">{{ ucfirst($product->product_type) }}</p>

                            @if($item->rental_start_date && $item->rental_end_date)
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $item->rental_start_date->format('M d') }} → {{ $item->rental_end_date->format('M d') }}
                                </p>
                            @endif

                            <div class="flex items-center gap-3 mt-3">
                                <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="100"
                                           class="w-16 px-2 py-1 border rounded text-sm text-center">
                                    <button type="submit" class="text-xs text-blue-600 hover:text-blue-800">
                                        {{ __('messages.cart_updated') }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('cart.remove', $item) }}" onsubmit="return confirm('Remove?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-800">
                                        {{ __('messages.cart_remove_btn') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="font-bold text-blue-600">{{ $currencyService->format($lineTotal, $displayCurrency) }}</p>
                            <p class="text-xs text-gray-400">{{ $currencyService->format($unitPrice, $displayCurrency) }} × {{ $item->quantity }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <aside class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border p-5 sticky top-4">
                    <h2 class="font-semibold text-gray-900 mb-4">{{ __('messages.cart_subtotal') }}</h2>

                    {{-- PATH-3C C1: Deposit calculation --}}
                    @php
                        $rentalDeposit = $items->filter(fn($i) => $i->product && $i->product->isRental())
                            ->sum(fn($i) => ($i->product->rentalDetail->rental_deposit ?? 0) * $i->quantity);
                    @endphp

                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-600">{{ __('messages.cart_subtotal') }}</span>
                        <span>{{ $currencyService->format($currencyService->convert($subtotal, 'NPR', $displayCurrency), $displayCurrency) }}</span>
                    </div>

                    @if($rentalDeposit > 0)
                        <div class="flex justify-between text-sm mb-2 text-gray-600">
                            <span>{{ __('messages.cart_deposit_refundable') }}</span>
                            <span class="font-medium">
                                {{ $currencyService->format($currencyService->convert($rentalDeposit, 'NPR', $displayCurrency), $displayCurrency) }}
                            </span>
                        </div>
                    @endif

                    <p class="text-2xl font-bold text-blue-600 mb-4 pt-2 border-t">
                        {{ $currencyService->format($currencyService->convert($subtotal + ($rentalDeposit ?? 0), 'NPR', $displayCurrency), $displayCurrency) }}
                    </p>
                    <a href="{{ route('checkout.index') }}"
                       class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
                        {{ __('messages.cart_checkout_btn') }}
                    </a>
                    <a href="{{ route('public.shop.index') }}" class="block text-center mt-3 text-sm text-gray-500 hover:text-blue-600">
                        {{ __('messages.cart_continue_shopping') }}
                    </a>
                </div>
            </aside>
        </div>
    @endif

</section>
@endsection