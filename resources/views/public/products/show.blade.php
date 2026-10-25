@extends('layouts.public')

@section('title', $product->name . ' | ' . __('messages.app_name'))
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 150))

@push('head')
<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
  '@@type' => 'Product',
  'name' => $product->name,
  'description' => \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 200),
  'image' => $product->cover_image ? (\filter_var($product->cover_image, FILTER_VALIDATE_URL) ? $product->cover_image : \Illuminate\Support\Facades\Storage::url($product->cover_image)) : null,
  'sku' => $product->slug,
  'brand' => $product->provider ? ['@@type' => 'Brand', 'name' => $product->provider->name] : null,
  'seller' => $product->provider ? ['@@type' => 'Organization', 'name' => $product->provider->name] : null,
  'offers' => [
    '@@type' => 'Offer',
    'price' => (string) $product->price,
    'priceCurrency' => $product->currency ?? 'NPR',
    'availability' => $product->status === 'active' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
    'url' => url()->current(),
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
  '@@type' => 'BreadcrumbList',
  'itemListElement' => [
    ['@@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
    ['@@type' => 'ListItem', 'position' => 2, 'name' => ucfirst($product->product_type), 'item' => url('/' . $product->product_type)],
    ['@@type' => 'ListItem', 'position' => 3, 'name' => $product->name, 'item' => url()->current()],
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')

@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
    $baseCurrency = $product->currency ?? 'NPR';
    $displayPrice = $currencyService->convert((float) $product->price, $baseCurrency, $displayCurrency);
    $formattedPrice = $currencyService->format($displayPrice, $displayCurrency);
    
    // Fix Image URL logic (Handle both local and external)
    $imgUrl = null;
    if ($product->cover_image) {
        $imgUrl = filter_var($product->cover_image, FILTER_VALIDATE_URL)
            ? $product->cover_image
            : Storage::url($product->cover_image);
    }
@endphp

<div class="bg-gray-50/50 min-h-screen pb-20">
    <section class="max-w-7xl mx-auto px-6 md:px-10 py-10">

        <!-- Breadcrumbs -->
        <nav class="flex items-center text-[11px] font-bold tracking-wider text-gray-400 uppercase mb-8">
            <a href="{{ url('/') }}" class="hover:text-gray-900 transition-colors">{{ __('messages.home') }}</a>
            <i class="fas fa-chevron-right text-[8px] mx-3"></i>
            @if($product->product_type === 'shop')
                <a href="{{ route('public.shop.index') }}" class="hover:text-gray-900 transition-colors">{{ __('messages.nav_shop') }}</a>
            @elseif($product->product_type === 'rental')
                <a href="{{ route('public.rental.index') }}" class="hover:text-gray-900 transition-colors">{{ __('messages.nav_rental') }}</a>
            @else
                <a href="{{ route('public.wholesale.index') }}" class="hover:text-gray-900 transition-colors">{{ __('messages.nav_wholesale') }}</a>
            @endif
            <i class="fas fa-chevron-right text-[8px] mx-3"></i>
            <span class="text-gray-900 truncate max-w-[200px]">{{ $product->name }}</span>
        </nav>

        <!-- Main Product Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 xl:gap-16 items-start">

            <!-- Left: Image Panel -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden relative aspect-square lg:aspect-auto lg:h-[600px] group">
                
                <!-- Category Badge -->
                <div class="absolute top-5 left-5 z-10">
                    @if($product->product_type === 'rental')
                        <span class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-amber-900 bg-amber-100/90 backdrop-blur-md rounded-xl shadow-sm border border-amber-200/50">Rental Gear</span>
                    @elseif($product->product_type === 'shop')
                        <span class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-emerald-900 bg-emerald-100/90 backdrop-blur-md rounded-xl shadow-sm border border-emerald-200/50">Handicraft</span>
                    @else
                        <span class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-purple-900 bg-purple-100/90 backdrop-blur-md rounded-xl shadow-sm border border-purple-200/50">Wholesale</span>
                    @endif
                </div>

                @if($imgUrl)
                    <img src="{{ $imgUrl }}" alt="{{ $product->name }}"
                         onerror="this.onerror=null; this.src='https://placehold.co/800x800/f3f4f6/a1a1aa?text={{ urlencode($product->name) }}';"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-in-out">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center bg-gray-50 text-gray-300">
                        <i class="fas fa-box-open text-6xl mb-4"></i>
                        <span class="text-sm font-medium">No Image Available</span>
                    </div>
                @endif
            </div>

            <!-- Right: Details Panel -->
            <div class="flex flex-col justify-center py-4">
                
                <!-- Provider Badge -->
                @if($product->provider)
                    <div class="mb-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-100 text-blue-700 text-xs font-bold tracking-wider uppercase">
                            <i class="fas fa-check-circle"></i>
                            {{ $product->provider->name }}
                        </span>
                    </div>
                @endif

                <!-- Title (Softer Color, Better Size) -->
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800 leading-snug mb-5 tracking-tight">{{ $product->name }}</h1>

                <!-- Price Box (Gradient Text + Card Style) -->
                <div class="flex items-end gap-2 mb-8 bg-gray-50/80 p-4 rounded-2xl border border-gray-100 inline-flex self-start shadow-sm">
                    <span class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
                        {{ $formattedPrice }}
                    </span>
                    @if($product->product_type === 'rental')
                        <span class="text-base font-semibold text-gray-500 mb-1">/ day</span>
                    @endif
                </div>

                <!-- Description -->
                @if($product->description)
                    <div class="mb-8">
                        <h3 class="text-sm font-bold text-gray-800 mb-2 uppercase tracking-wider">{{ __('messages.products_detail_about') }}</h3>
                        <p class="text-gray-600 leading-relaxed whitespace-pre-line text-sm">{{ $product->description }}</p>
                    </div>
                @endif

                <!-- Info Badges (Stock, Deposit, Min Order) -->
                <div class="flex flex-wrap gap-3 mb-8">
                    @if($product->isShop() && $product->shopDetail?->stock_count !== null)
                        @if($product->shopDetail->stock_count > 0)
                            <div class="px-4 py-2 bg-green-50 rounded-xl border border-green-100 flex items-center gap-2 text-green-700 font-semibold text-sm">
                                <i class="fas fa-check-circle"></i> {{ __('messages.product_in_stock', ['count' => $product->shopDetail->stock_count]) }}
                            </div>
                        @else
                            <div class="px-4 py-2 bg-red-50 rounded-xl border border-red-100 flex items-center gap-2 text-red-700 font-semibold text-sm">
                                <i class="fas fa-times-circle"></i> {{ __('messages.product_out_of_stock') }}
                            </div>
                        @endif
                    @endif

                    @if($product->product_type === 'rental' && $product->rentalDetail && $product->rentalDetail->rental_deposit)
                        <div class="px-4 py-2 bg-gray-100 rounded-xl border border-gray-200 flex items-center gap-2 text-gray-700 font-semibold text-sm">
                            <i class="fas fa-shield-alt text-gray-400"></i> {{ __('messages.products_deposit_label') }}: {{ $currencyService->format((float)$product->rentalDetail->rental_deposit, $baseCurrency) }}
                        </div>
                    @endif

                    @if($product->product_type === 'wholesale' && $product->wholesaleDetail)
                        <div class="px-4 py-2 bg-blue-50 rounded-xl border border-blue-100 flex items-center gap-2 text-blue-700 font-semibold text-sm">
                            <i class="fas fa-boxes"></i> {{ __('messages.products_min_order') }}: {{ $product->wholesaleDetail->min_order_qty }}
                        </div>
                    @endif
                </div>

                <!-- Form Section (Add to Cart / Rent) -->
                @if($product->isShop() && $product->shopDetail?->stock_count === 0)
                    <div class="mt-2">
                        <button type="button" disabled class="w-full bg-gray-200 text-gray-500 py-4 rounded-xl font-bold text-lg cursor-not-allowed">
                            {{ __('messages.product_out_of_stock') }}
                        </button>
                    </div>
                @else
                <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                    @csrf

                    @if($product->product_type === 'rental')
                        @php $rentalDetail = $product->rentalDetail; @endphp
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-900 mb-3 uppercase tracking-wider">
                                {{ __('messages.rental_select_dates') }}
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <input type="date" name="rental_start_date" required min="{{ date('Y-m-d') }}"
                                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-gray-900 focus:border-gray-900 text-sm font-medium transition-all">
                                    <p class="text-xs text-gray-500 mt-1.5 ml-1">{{ __('messages.rental_start_label') }}</p>
                                </div>
                                <div>
                                    <input type="date" name="rental_end_date" required min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-gray-900 focus:border-gray-900 text-sm font-medium transition-all">
                                    <p class="text-xs text-gray-500 mt-1.5 ml-1">{{ __('messages.rental_end_label') }}</p>
                                </div>
                            </div>
                            
                            @if($rentalDetail && ($rentalDetail->rental_min_days || $rentalDetail->rental_max_days))
                                <p class="text-xs text-gray-500 mt-3 flex items-center gap-1.5">
                                    <i class="fas fa-info-circle"></i>
                                    {{ __('messages.rental_duration_hint', ['min' => $rentalDetail->rental_min_days ?? 1, 'max' => $rentalDetail->rental_max_days ?? '∞']) }}
                                </p>
                            @endif

                            @if($rentalDetail && ($rentalDetail->late_fee_per_day > 0 || $rentalDetail->damage_deposit_pct < 100))
                                <div class="mt-4 p-4 bg-amber-50 rounded-xl border border-amber-100 text-xs space-y-1.5">
                                    <div class="font-bold text-amber-900 mb-2 uppercase tracking-wide">{{ __('messages.rental_terms_heading') }}</div>
                                    @if($rentalDetail->rental_deposit > 0)
                                        <div class="text-amber-800 flex justify-between"><span>Deposit:</span> <span>{{ $baseCurrency }} {{ number_format($rentalDetail->rental_deposit, 2) }}</span></div>
                                    @endif
                                    @if($rentalDetail->late_fee_per_day > 0)
                                        <div class="text-amber-800 flex justify-between"><span>Late Fee:</span> <span>{{ $baseCurrency }} {{ number_format($rentalDetail->late_fee_per_day, 2) }}/day</span></div>
                                    @endif
                                    @if($rentalDetail->damage_deposit_pct < 100)
                                        <div class="text-amber-800 flex justify-between"><span>Damage Deduction:</span> <span>{{ $rentalDetail->damage_deposit_pct }}%</span></div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="flex gap-3 items-center">
                        <div class="relative w-24">
                            <input type="number" name="quantity" value="1" min="1" max="100"
                                   class="w-full text-center px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-gray-900 focus:border-gray-900 text-lg font-bold transition-all">
                        </div>
                        <button type="submit" class="flex-1 bg-gray-900 hover:bg-black text-white py-3.5 rounded-xl font-bold text-lg shadow-lg shadow-gray-900/20 transition-all hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                            <i class="fas fa-shopping-cart text-sm"></i> {{ __('messages.cart_add_btn') }}
                        </button>
                    </div>

                    @if(session('error'))
                        <p class="mt-3 text-sm text-red-600 font-medium">{{ session('error') }}</p>
                    @endif
                    @if(session('success'))
                        <p class="mt-3 text-sm text-green-600 font-medium">{{ session('success') }}</p>
                    @endif
                </form>
                @endif

                @if($product->product_type === 'wholesale' && auth()->check())
                    <a href="{{ route('wholesale.rfq.create', $product) }}"
                       class="block mt-4 text-center bg-white border-2 border-amber-500 text-amber-600 hover:bg-amber-50 py-3.5 rounded-xl font-bold text-lg transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-file-invoice"></i> {{ __('messages.rfq_request_quote_btn') }}
                    </a>
                @endif

            </div>
        </div>
    </section>
</div>

@endsection