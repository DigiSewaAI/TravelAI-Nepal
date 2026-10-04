@extends('layouts.public')

@section('title', __('messages.checkout_title') . ' | ' . __('messages.app_name'))

@section('content')
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
@endphp

<section class="max-w-5xl mx-auto px-6 md:px-10 py-8">

    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('messages.checkout_title') }}</h1>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-800 border border-red-300 rounded-lg">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                {{-- Contact --}}
                <div class="bg-white rounded-xl shadow-sm border p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">{{ __('messages.checkout_contact_section') }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.checkout_contact_name') }} *</label>
                            <input type="text" name="contact_name" required maxlength="100"
                                   value="{{ old('contact_name', auth()->user()->name ?? '') }}"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('contact_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.checkout_contact_email') }} *</label>
                            <input type="email" name="contact_email" required maxlength="150"
                                   value="{{ old('contact_email', auth()->user()->email ?? '') }}"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('contact_email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.checkout_contact_phone') }}</label>
                            <input type="text" name="contact_phone" maxlength="20"
                                   value="{{ old('contact_phone', auth()->user()->phone ?? '') }}"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                {{-- Shipping --}}
                <div class="bg-white rounded-xl shadow-sm border p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">{{ __('messages.checkout_shipping_section') }}</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.checkout_shipping_address') }}</label>
                            <input type="text" name="shipping_address" maxlength="255"
                                   value="{{ old('shipping_address') }}"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.checkout_shipping_city') }}</label>
                                <input type="text" name="shipping_city" maxlength="100"
                                       value="{{ old('shipping_city') }}"
                                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.checkout_shipping_country') }}</label>
                                <input type="text" name="shipping_country" maxlength="60"
                                       value="{{ old('shipping_country', 'Nepal') }}"
                                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.checkout_notes') }}</label>
                            <textarea name="notes" rows="3" maxlength="1000"
                                      class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Summary --}}
            <aside class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border p-5 sticky top-4">
                    <h2 class="font-semibold text-gray-900 mb-4">{{ __('messages.checkout_cart_summary') }}</h2>

                    <div class="space-y-2 mb-4">
                        @foreach($items as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ $item->product->name }} × {{ $item->quantity }}</span>
                                <span class="font-medium">
                                    {{ $currencyService->format(
                                        $currencyService->convert($item->subtotal, $item->product->currency ?? 'NPR', $displayCurrency),
                                        $displayCurrency
                                    ) }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t pt-3 flex justify-between text-lg font-bold">
                        <span>{{ __('messages.cart_subtotal') }}</span>
                        <span class="text-blue-600">
                            {{ $currencyService->format(
                                $currencyService->convert($subtotal, 'NPR', $displayCurrency),
                                $displayCurrency
                            ) }}
                        </span>
                    </div>

                    <button type="submit"
                            class="w-full mt-5 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
                        <i class="fas fa-check mr-2"></i> {{ __('messages.checkout_place_order') }}
                    </button>

                    <a href="{{ route('cart.index') }}" class="block text-center mt-3 text-sm text-gray-500 hover:text-blue-600">
                        ← {{ __('messages.cart_title') }}
                    </a>
                </div>
            </aside>

        </div>
    </form>

</section>
@endsection