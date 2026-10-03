@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
    $baseCurrency = $product->currency ?? 'NPR';
    $displayPrice = $currencyService->convert((float) $product->price, $baseCurrency, $displayCurrency);
    $formattedPrice = $currencyService->format($displayPrice, $displayCurrency);
@endphp

<a href="{{ route('public.products.show', $product->slug) }}"
   class="group bg-white rounded-xl shadow-sm border hover:shadow-md transition overflow-hidden flex flex-col">

    <div class="bg-gray-100 overflow-hidden">
        @if($product->cover_image)
            <img src="{{ asset('storage/' . $product->cover_image) }}" alt="{{ $product->name }}"
                 class="w-full h-48 object-cover group-hover:scale-105 transition-transform">
        @else
            <div class="w-full h-48 flex items-center justify-center bg-gray-100 text-gray-400">
                <i class="fas fa-image text-4xl"></i>
            </div>
        @endif
    </div>

    <div class="p-4 flex-1 flex flex-col">
        <h3 class="font-semibold text-gray-900 line-clamp-2">{{ $product->name }}</h3>

        @if($product->provider)
            <p class="text-xs text-gray-500 mt-1">{{ $product->provider->name }}</p>
        @endif

        <div class="mt-auto pt-3 flex items-end justify-between gap-2">
            <div>
                <span class="text-lg font-bold text-blue-600">{{ $formattedPrice }}</span>
                @if($product->product_type === 'rental')
                    <span class="text-xs text-gray-500">{{ __('messages.products_per_day') }}</span>
                @endif
            </div>

            @if($product->product_type === 'wholesale' && $product->wholesaleDetail)
                <span class="text-xs text-gray-500">
                    {{ __('messages.products_min_order') }}: {{ $product->wholesaleDetail->min_order_qty }}
                </span>
            @endif

            @if($product->product_type === 'rental' && $product->rentalDetail && $product->rentalDetail->rental_deposit)
                <span class="text-xs text-gray-500">
                    {{ __('messages.products_deposit_label') }}: {{ $currencyService->format((float)$product->rentalDetail->rental_deposit, $baseCurrency) }}
                </span>
            @endif
        </div>
    </div>
</a>