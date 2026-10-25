@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
    $baseCurrency = $product->currency ?? 'NPR';
    $displayPrice = $currencyService->convert((float) $product->price, $baseCurrency, $displayCurrency);
    $formattedPrice = $currencyService->format($displayPrice, $displayCurrency);

    // Image URL handling (local vs external Unsplash links)
    $imgUrl = null;
    if ($product->cover_image) {
        $imgUrl = filter_var($product->cover_image, FILTER_VALIDATE_URL)
            ? $product->cover_image
            : Storage::url($product->cover_image);
    }
@endphp

<a href="{{ route('public.products.show', $product->slug) }}"
   class="group bg-white rounded-2xl border border-gray-100 hover:border-blue-200 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col relative">

    <!-- Category Badge -->
    <div class="absolute top-3 left-3 z-10">
        @if($product->product_type === 'rental')
            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-800 bg-amber-100/90 backdrop-blur-sm rounded-md shadow-sm">Rental Gear</span>
        @elseif($product->product_type === 'shop')
            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-100/90 backdrop-blur-sm rounded-md shadow-sm">Handicraft</span>
        @else
            <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-purple-800 bg-purple-100/90 backdrop-blur-sm rounded-md shadow-sm">Wholesale</span>
        @endif
    </div>

    <!-- Image -->
    <div class="bg-gray-100 overflow-hidden h-52 relative">
        @if($imgUrl)
            <img src="{{ $imgUrl }}" alt="{{ $product->name }}"
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 ease-in-out">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center bg-gray-50 text-gray-300 group-hover:bg-gray-100 transition-colors">
                <i class="fas fa-box-open text-3xl mb-2"></i>
                <span class="text-xs font-medium">No Image</span>
            </div>
        @endif
    </div>

    <!-- Content -->
    <div class="p-5 flex-1 flex flex-col">
        <h3 class="font-bold text-gray-900 text-lg leading-tight line-clamp-2 group-hover:text-blue-600 transition-colors">{{ $product->name }}</h3>

        @if($product->provider)
            <p class="text-sm text-gray-500 mt-1.5 flex items-center gap-1.5">
                <i class="fas fa-store text-xs opacity-70"></i>
                <span class="truncate">{{ $product->provider->name }}</span>
            </p>
        @endif

        <div class="mt-auto pt-4 flex items-end justify-between gap-2 border-t border-gray-100 mt-4">
            <div>
                <span class="text-xl font-extrabold text-gray-900">{{ $formattedPrice }}</span>
                @if($product->product_type === 'rental')
                    <span class="text-sm font-semibold text-gray-500">/ day</span>
                @endif
            </div>

            @if($product->product_type === 'wholesale' && $product->wholesaleDetail)
                <span class="text-xs font-medium bg-blue-50 text-blue-700 px-2 py-1 rounded">
                    Min: {{ $product->wholesaleDetail->min_order_qty }}
                </span>
            @endif

            @if($product->product_type === 'rental' && $product->rentalDetail && $product->rentalDetail->rental_deposit)
                <span class="text-xs font-medium text-gray-500">
                    Dep: {{ $currencyService->format((float)$product->rentalDetail->rental_deposit, $baseCurrency) }}
                </span>
            @endif
        </div>
    </div>
</a>