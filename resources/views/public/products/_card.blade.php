@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
    $baseCurrency = $product->currency ?? 'NPR';
    $displayPrice = $currencyService->convert((float) $product->price, $baseCurrency, $displayCurrency);
    $formattedPrice = $currencyService->format($displayPrice, $displayCurrency);

    $imgUrl = null;
    if ($product->cover_image) {
        $imgUrl = filter_var($product->cover_image, FILTER_VALIDATE_URL)
            ? $product->cover_image
            : Storage::url($product->cover_image);
    }

    $badgeConfig = [
        'rental'    => ['label' => 'Rental Gear', 'bg' => 'bg-amber-100',   'text' => 'text-amber-800',   'dot' => 'bg-amber-500'],
        'shop'      => ['label' => 'Handicraft',  'bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'dot' => 'bg-emerald-500'],
        'wholesale' => ['label' => 'Wholesale',   'bg' => 'bg-blue-100',    'text' => 'text-blue-800',    'dot' => 'bg-blue-500'],
    ];
    $badge = $badgeConfig[$product->product_type] ?? $badgeConfig['wholesale'];

    // SVG art by product category/name keyword
    $nameLower = strtolower($product->name);
    $artKey = 'box';
    if (str_contains($nameLower, 'shoe') || str_contains($nameLower, 'boot')) $artKey = 'shoe';
    elseif (str_contains($nameLower, 'bag') || str_contains($nameLower, 'sleeping')) $artKey = 'bag';
    elseif (str_contains($nameLower, 'glass')) $artKey = 'glasses';
    elseif (str_contains($nameLower, 'jacket') || str_contains($nameLower, 'down')) $artKey = 'jacket';
    elseif (str_contains($nameLower, 'handicraft') || str_contains($nameLower, 'craft')) $artKey = 'craft';
    elseif (str_contains($nameLower, 'tea') || str_contains($nameLower, 'coffee')) $artKey = 'tea';

    $svgArt = [
        'shoe' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" class="w-24 h-24"><path d="M9 48V32c0-3.4 3.4-5.4 6.4-3.6L27 35l12.5 2.2C48 38.4 55 43.5 55 51v1.5c0 1.7-1.4 3-3.1 3H12.1C10.4 55.5 9 54.1 9 52.4V48Z"/><path d="M9 44h45"/><path d="M21 32.5l2.8 6M28.5 33.8l2.2 6"/></svg>',
        'bag' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" class="w-24 h-24"><path d="M22 8h20a7 7 0 0 1 7 7v34a7 7 0 0 1-7 7H22a7 7 0 0 1-7-7V15a7 7 0 0 1 7-7Z"/><path d="M15 21h34"/><path d="M32 21v35"/><path d="M22 8v13M42 8v13"/></svg>',
        'glasses' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" class="w-24 h-24"><circle cx="19" cy="35" r="11"/><circle cx="45" cy="35" r="11"/><path d="M30 34h4"/><path d="M8 30 3 24"/><path d="M56 30l5-6"/></svg>',
        'jacket' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" class="w-24 h-24"><path d="M23 9 11 17 6.5 32l7.5 3v18h36V35l7.5-3L53 17 41 9l-4 6h-10l-4-6Z"/><path d="M32 15v38"/><path d="M26 24h-4M42 24h-4"/></svg>',
        'craft' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" class="w-24 h-24"><path d="M17 22h30l-2.8 29.2a4.5 4.5 0 0 1-4.5 4.1H24.3a4.5 4.5 0 0 1-4.5-4.1L17 22Z"/><path d="M12 22h40"/><path d="M25 33h14M25 42h14"/><path d="M26 22c0-5 3-8 6-8s6 3 6 8"/></svg>',
        'tea' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" class="w-24 h-24"><path d="M13 25h31v13a15 15 0 0 1-15 15h-1a15 15 0 0 1-15-15V25Z"/><path d="M44 29h4a6.5 6.5 0 0 1 0 13h-4"/><path d="M22 16c0-2.5 3-3.5 3-6M32 16c0-2.5 3-3.5 3-6"/><path d="M11 58h36"/></svg>',
        'box' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" class="w-24 h-24"><path d="M32 8 8 20v24l24 12 24-12V20L32 8Z"/><path d="M8 20l24 12 24-12M32 32v24"/></svg>',
    ];
@endphp

<a href="{{ route('public.products.show', $product->slug) }}"
   class="group relative bg-white rounded-2xl border border-gray-100 hover:border-blue-300 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">

    <div class="relative overflow-hidden h-56 bg-gradient-to-br from-blue-50 via-indigo-50 to-blue-100 flex items-center justify-center">
        @if($imgUrl)
            <img src="{{ $imgUrl }}" alt="{{ $product->name }}"
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 ease-in-out">
        @else
            <div class="text-blue-300 group-hover:text-blue-500 group-hover:scale-110 transition-all duration-500">
                {!! $svgArt[$artKey] !!}
            </div>
        @endif

        <div class="absolute top-3 left-3 z-10">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badge['bg'] }} {{ $badge['text'] }} backdrop-blur-sm rounded-md shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                {{ $badge['label'] }}
            </span>
        </div>

        @if($product->location && $product->location->city)
            <div class="absolute top-3 right-3 z-10">
                <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-semibold text-gray-700 bg-white/90 backdrop-blur-sm rounded-md shadow-sm">
                    <i class="fas fa-map-marker-alt text-[9px] text-blue-600"></i>
                    {{ $product->location->city }}
                </span>
            </div>
        @endif
    </div>

    <div class="p-5 flex-1 flex flex-col">
        @if($product->provider)
            <div class="flex items-center gap-1.5 text-[11.5px] font-semibold text-gray-400 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_0_3px_rgba(16,185,129,0.15)]"></span>
                <span class="truncate">{{ $product->provider->name }}</span>
            </div>
        @endif

        <h3 class="font-bold text-gray-900 text-base leading-snug line-clamp-2 group-hover:text-blue-600 transition-colors min-h-[2.75rem]">
            {{ $product->name }}
        </h3>

        <div class="flex flex-wrap gap-1.5 mt-3 mb-4">
            @if($product->product_type === 'wholesale' && $product->wholesaleDetail)
                <span class="inline-flex items-center text-[10.5px] font-bold bg-blue-50 text-blue-700 border border-blue-100 px-2 py-0.5 rounded-md">
                    Min {{ $product->wholesaleDetail->min_order_qty }} units
                </span>
            @endif
            @if($product->product_type === 'rental' && $product->rentalDetail && $product->rentalDetail->rental_deposit)
                <span class="inline-flex items-center text-[10.5px] font-semibold bg-amber-50 text-amber-700 border border-amber-100 px-2 py-0.5 rounded-md">
                    Deposit: {{ $currencyService->format((float)$product->rentalDetail->rental_deposit, $baseCurrency) }}
                </span>
            @endif
            @if($product->product_type === 'shop')
                <span class="inline-flex items-center text-[10.5px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 px-2 py-0.5 rounded-md">
                    {{ __('messages.handcrafted') }}
                </span>
            @endif
        </div>

        <div class="mt-auto pt-4 flex items-end justify-between gap-2 border-t border-gray-100">
            <div class="flex items-baseline gap-1">
                <span class="text-xl font-extrabold text-gray-900">{{ $formattedPrice }}</span>
                @if($product->product_type === 'rental')
                    <span class="text-xs font-semibold text-gray-400">{{ __('messages.day') }}</span>
                @else
                    <span class="text-xs font-semibold text-gray-400">{{ __('messages.unit') }}</span>
                @endif
            </div>
            @if($product->product_type === 'wholesale')
                <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 bg-blue-50 group-hover:bg-blue-600 group-hover:text-white px-3 py-1.5 rounded-lg transition-all">
                    <i class="fas fa-file-invoice text-[10px]"></i>
                    {{ __('messages.request_quote') }}
                    <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                </span>
            @elseif($product->product_type === 'rental')
                <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 bg-blue-50 group-hover:bg-blue-600 group-hover:text-white px-3 py-1.5 rounded-lg transition-all">
                    <i class="fas fa-calendar-check text-[10px]"></i>
                    Book Now
                    <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                </span>
            @else
                <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 bg-blue-50 group-hover:bg-blue-600 group-hover:text-white px-3 py-1.5 rounded-lg transition-all">
                    <i class="fas fa-shopping-bag text-[10px]"></i>{{ __('messages.add_to_cart') }}<i class="fas fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                </span>
            @endif
        </div>
    </div>
</a>