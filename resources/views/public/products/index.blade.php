@extends('layouts.public')

@php
    $typeTitles = [
        'shop'      => __('messages.shop_index_title'),
        'rental'    => __('messages.rental_index_title'),
        'wholesale' => __('messages.wholesale_index_title'),
    ];
    $typeSubtitles = [
        'shop'      => __('messages.shop_index_subtitle'),
        'rental'    => __('messages.rental_index_subtitle'),
        'wholesale' => __('messages.wholesale_index_subtitle'),
    ];
    $typeEyebrows = [
        'shop'      => 'Verified Artisans · Direct from Nepal',
        'rental'    => 'Adventure Ready · Trusted Gear',
        'wholesale' => 'B2B Marketplace · Verified Suppliers',
    ];
@endphp

@section('title', ($typeTitles[$type] ?? '') . ' | ' . __('messages.app_name'))
@section('meta_description', $typeSubtitles[$type] ?? '')

@section('content')

<section class="relative overflow-hidden bg-gradient-to-br from-white via-blue-50/40 to-white pt-14 pb-24 border-b border-gray-100">
    <div class="absolute inset-0 opacity-30 pointer-events-none"
         style="background-image:radial-gradient(circle at 20% 0%, rgba(59,130,246,.08), transparent 50%), radial-gradient(circle at 80% 100%, rgba(99,102,241,.08), transparent 50%);"></div>
    <div class="max-w-7xl mx-auto px-6 md:px-10 relative">
        <span class="inline-flex items-center gap-2 text-[11px] font-bold tracking-[1.5px] uppercase text-blue-600 bg-blue-50 border border-blue-100 rounded-full px-4 py-1.5 mb-6">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
            {{ $typeEyebrows[$type] ?? 'Verified Marketplace' }}
        </span>
        <h1 class="text-4xl md:text-6xl font-black text-gray-900 tracking-tight leading-none mb-5 max-w-4xl">
            {{ $typeTitles[$type] ?? '' }}
        </h1>
        <p class="text-lg md:text-xl text-gray-500 font-medium max-w-2xl leading-relaxed">
            {{ $typeSubtitles[$type] ?? '' }}
        </p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-10 max-w-3xl">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-lg transition-all">
                <div class="text-2xl font-black text-blue-600">{{ $products->total() }}</div>
                <div class="text-[10.5px] text-gray-500 uppercase tracking-wider font-bold mt-1">Products</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-lg transition-all">
                <div class="text-2xl font-black text-blue-600">{{ $locations->count() }}</div>
                <div class="text-[10.5px] text-gray-500 uppercase tracking-wider font-bold mt-1">Locations</div>
            </div>
        </div>
    </div>
</section>

<div class="bg-gray-50/50 min-h-screen pt-16 pb-20">
    <section class="max-w-7xl mx-auto px-6 md:px-10">

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-10 xl:gap-14 items-start">

            <aside class="lg:col-span-1">
                @include('public.products._filters', ['type' => $type, 'locations' => $locations])
            </aside>

            <main class="lg:col-span-3">
                @if($products->count() > 0)

                    <div class="flex items-center justify-between mb-6 mt-2 bg-white rounded-xl border border-gray-100 px-6 py-4 shadow-sm">
                        <span class="text-sm font-semibold text-gray-600">
                            Showing <span class="text-blue-600 font-bold">{{ $products->firstItem() }}-{{ $products->lastItem() }}</span>
                            of <span class="text-blue-600 font-bold">{{ $products->total() }}</span> results
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($products as $product)
                            @include('public.products._card', ['product' => $product])
                        @endforeach
                    </div>

                    <div class="mt-12 flex justify-center">
                        {{ $products->links() }}
                    </div>
<style>
.btn-vm-premium {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 17px 36px;
    border-radius: 14px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.2px;
    color: #ffffff;
    overflow: hidden;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    box-shadow: 0 22px 44px -20px rgba(37, 99, 235, 1), inset 0 1px 0 rgba(255, 255, 255, 0.55);
    transition: transform 0.3s cubic-bezier(0.22, 0.85, 0.3, 1), box-shadow 0.3s cubic-bezier(0.22, 0.85, 0.3, 1);
    border: none;
    cursor: pointer;
    white-space: nowrap;
}
.btn-vm-premium .btn-vm-shine {
    position: absolute;
    top: 0;
    left: -60%;
    width: 45%;
    height: 100%;
    background: linear-gradient(100deg, transparent, rgba(255, 255, 255, 0.55), transparent);
    transform: skewX(-18deg);
    transition: left 0.7s cubic-bezier(0.22, 0.85, 0.3, 1);
    pointer-events: none;
}
.btn-vm-premium:hover .btn-vm-shine {
    left: 130%;
}
.btn-vm-premium:hover {
    transform: translateY(-3px) scale(1.015);
    box-shadow: 0 30px 56px -22px rgba(37, 99, 235, 1), inset 0 1px 0 rgba(255, 255, 255, 0.6);
}
.btn-vm-premium:active {
    transform: translateY(-1px) scale(1);
}
.btn-vm-premium .btn-vm-text {
    position: relative;
    z-index: 1;
}
.btn-vm-premium .btn-vm-count {
    position: relative;
    z-index: 1;
    font-family: inherit;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.5px;
    background: rgba(255, 255, 255, 0.22);
    padding: 4px 9px;
    border-radius: 99px;
}
.btn-vm-premium .btn-vm-arrow {
    position: relative;
    z-index: 1;
    width: 16px;
    height: 16px;
    transition: transform 0.3s cubic-bezier(0.22, 0.85, 0.3, 1);
}
.btn-vm-premium:hover .btn-vm-arrow {
    transform: translateX(5px);
}
</style>
{{-- ========== VIEW MORE PRODUCTS (Premium) ========== --}}
<div class="mt-12 flex flex-col items-center gap-3">
    <button type="button"
            onclick="alert('Full catalogue coming soon — 24+ new SKUs weekly')"
            class="btn-vm-premium">
        <span class="btn-vm-shine"></span>
        <span class="btn-vm-text">View More Products</span>
        <span class="btn-vm-count">+6</span>
        <svg class="btn-vm-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14M13 6l6 6-6 6"/>
        </svg>
    </button>
    <p class="text-[11.5px] font-semibold text-gray-400 text-center">
        Full catalogue coming soon · <span class="text-blue-600 font-bold">24+ new SKUs</span> added weekly
    </p>
</div>

                @else
                    <div class="flex flex-col items-center justify-center py-24 bg-white rounded-3xl border border-dashed border-gray-300 shadow-sm">
                        <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-5">
                            <i class="fas fa-search text-3xl text-blue-400"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No products found</h3>
                        <p class="text-gray-500 text-sm max-w-md text-center mb-6">{{ __('messages.products_no_items') }} Try adjusting your filters or search terms.</p>
                        <a href="{{ url()->current() }}" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-md">
                            Clear all filters
                        </a>
                    </div>
                @endif
            </main>

        </div>
    </section>
</div>

{{-- ═══════════ CAROUSEL — right-to-left infinite scroll ═══════════ --}}
@if($products->count() > 0)
<section class="relative py-16 bg-gradient-to-b from-white to-blue-50/40 border-t border-gray-100 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 md:px-10 mb-8">
        <span class="inline-flex items-center gap-2 text-[11px] font-bold tracking-[1.5px] uppercase text-blue-600 bg-blue-50 border border-blue-100 rounded-full px-4 py-1.5 mb-4">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
            Live Marketplace
        </span>
        <h2 class="text-3xl md:text-4xl font-black text-gray-900 tracking-tight leading-tight">
            Continuously sourcing, <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">always in motion</span>.
        </h2>
        <p class="text-gray-500 text-base mt-3 max-w-2xl">
            Hand-picked {{ $type }} lots moving through the network. Hover to pause and click any card to view details.
        </p>
    </div>

    <div class="relative marquee-container" style="mask-image:linear-gradient(90deg,transparent 0,#000 5%,#000 95%,transparent 100%);-webkit-mask-image:linear-gradient(90deg,transparent 0,#000 5%,#000 95%,transparent 100%);">
        <div class="flex w-max marquee-track" style="gap:24px;">
            @foreach($products as $product)
                <a href="{{ route('public.products.show', $product->slug) }}"
                   class="flex-shrink-0 w-64 bg-white rounded-2xl border border-gray-100 hover:border-blue-300 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden group">
                    <div class="h-32 bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center relative">
                        @php
                            $imgUrl = null;
                            if ($product->cover_image) {
                                $imgUrl = filter_var($product->cover_image, FILTER_VALIDATE_URL)
                                    ? $product->cover_image
                                    : Storage::url($product->cover_image);
                            }
                        @endphp
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-cube text-blue-300 text-4xl group-hover:text-blue-500 transition-colors"></i>
                        @endif
                    </div>
                    <div class="p-4">
                        <h4 class="font-bold text-sm text-gray-900 line-clamp-2 group-hover:text-blue-600 transition-colors min-h-[2.5rem]">{{ $product->name }}</h4>
                        <p class="text-[11px] text-gray-400 font-semibold mt-1 truncate">{{ $product->provider->name ?? '' }}</p>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                            <span class="text-sm font-extrabold text-blue-600">{{ $product->currency ?? 'NPR' }} {{ number_format($product->price) }}</span>
                            <i class="fas fa-arrow-right text-xs text-blue-400 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </a>
            @endforeach
            {{-- Duplicate for seamless loop --}}
            @foreach($products as $product)
                <a href="{{ route('public.products.show', $product->slug) }}"
                   class="flex-shrink-0 w-64 bg-white rounded-2xl border border-gray-100 hover:border-blue-300 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden group">
                    <div class="h-32 bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center">
                        <i class="fas fa-cube text-blue-300 text-4xl"></i>
                    </div>
                    <div class="p-4">
                        <h4 class="font-bold text-sm text-gray-900 line-clamp-2 min-h-[2.5rem]">{{ $product->name }}</h4>
                        <p class="text-[11px] text-gray-400 font-semibold mt-1 truncate">{{ $product->provider->name ?? '' }}</p>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                            <span class="text-sm font-extrabold text-blue-600">{{ $product->currency ?? 'NPR' }} {{ number_format($product->price) }}</span>
                            <i class="fas fa-arrow-right text-xs text-blue-400"></i>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Keyframes for marquee --}}
<style>
@keyframes marquee {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
}
.marquee-track {
    animation: marquee 22s linear infinite;
    will-change: transform;
}
.marquee-container:hover .marquee-track {
    animation-play-state: paused;
}
@media (prefers-reduced-motion: reduce) {
    .marquee-track { animation: none !important; }
}
</style>

@endif


@endsection
