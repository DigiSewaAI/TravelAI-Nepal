@extends('layouts.public')

@section('title', __('messages.explore_page_title') . ' | ' . __('messages.app_name'))
@section('meta_description', __('messages.explore_page_description'))
@section('meta_keywords', 'Nepal treks, tours, hotels, activities, adventures, Himalayan trekking')

@push('head')
{{-- ========== Canonical + hreflang ========== --}}
<link rel="canonical" href="{{ url()->current() }}">
<link rel="alternate" hreflang="en" href="{{ url()->current() }}">
<link rel="alternate" hreflang="ne" href="{{ route('lang.switch', 'np') }}">
<link rel="alternate" hreflang="hi" href="{{ route('lang.switch', 'hi') }}">
<link rel="alternate" hreflang="zh-Hans" href="{{ route('lang.switch', 'zh') }}">
<link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

{{-- ========== JSON-LD: ItemList (SEO) ========== --}}
@verbatim
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "ItemList",
  "numberOfItems": {{ $services->total() }},
  "itemListElement": [
    @foreach($services->take(10) as $i => $s)
    {
      "@@type": "ListItem",
      "position": {{ $i + 1 }},
      "name": "{{ addslashes($s->name) }}",
      "url": "{{ route('public.services.show', $s->slug) }}"
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ]
}
</script>
@endverbatim

{{-- ========== Leaflet + Globe.gl (async) ========== --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
<script src="//unpkg.com/globe.gl" defer></script>
<script src="//unpkg.com/three" defer></script>

<style>
    /* ═══════════════ GLOBE (Responsive) ═══════════════ */
    .globe-wrap-premium {
        position: relative;
        width: 100%;
        height: clamp(300px, 50vw, 600px);
        max-width: 600px;
        margin: 0 auto;
    }
    .globe-glow-premium { position: absolute; inset: 8%; border-radius: 50%; background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, transparent 65%); pointer-events: none; }
    #globeViz { width: 100%; height: 100%; border-radius: 50%; cursor: grab; position: relative; z-index: 1; }
    #globeViz:active { cursor: grabbing; }
    .globe-btn {
        width: 40px; height: 40px; border-radius: 50%;
        background: rgba(255,255,255,.95); backdrop-filter: blur(10px);
        border: 1px solid rgba(0,0,0,.08);
        display: grid; place-items: center; color: #4b5563;
        transition: all .25s ease; box-shadow: 0 4px 12px rgba(0,0,0,.08);
    }
    .globe-btn:hover, .globe-btn.active {
        background: #2563eb; color: #fff; border-color: #2563eb;
        transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,.35);
    }

    /* ═══════════════ MAP ═══════════════ */
    #nepalMap { height: clamp(320px, 45vw, 520px); width: 100%; background: #f3f4f6; border-radius: 1rem; }
    .custom-pin {
        width: 32px; height: 32px; border-radius: 50%; border: 2.5px solid; background: #fff;
        display: grid; place-items: center; font-size: 13px;
        box-shadow: 0 4px 12px rgba(0,0,0,.15), 0 0 0 6px rgba(59,130,246,.08);
        transition: all .25s ease; cursor: pointer;
    }
    .custom-pin:hover { transform: scale(1.25); box-shadow: 0 8px 24px rgba(0,0,0,.25), 0 0 0 10px rgba(59,130,246,.15); }
    .custom-pin.trek { border-color:#2563eb; color:#2563eb; }
    .custom-pin.tour { border-color:#7c3aed; color:#7c3aed; }
    .custom-pin.activity { border-color:#f59e0b; color:#f59e0b; }
    .custom-pin.pilgrimage { border-color:#ec4899; color:#ec4899; }
    .custom-pin.wildlife { border-color:#10b981; color:#10b981; }
    .leaflet-popup-content-wrapper { background:#fff; border-radius:14px; box-shadow:0 12px 32px rgba(0,0,0,.15); border:1px solid rgba(0,0,0,.05); }
    .leaflet-popup-content { margin:14px 16px; font-family:'Inter',sans-serif; font-size:13px; }
    .leaflet-popup-tip { background:#fff; }
    .leaflet-popup-close-button { color:#6b7280 !important; }
    .leaflet-control-zoom a { background:#fff !important; color:#374151 !important; }
    .leaflet-control-zoom a:hover { background:#2563eb !important; color:#fff !important; }

    /* ═══════════════ CARDS ═══════════════ */
    .premium-card { transition: transform .4s cubic-bezier(.16,1,.3,1), box-shadow .4s cubic-bezier(.16,1,.3,1), border-color .3s ease; }
    .premium-card:hover { transform: translateY(-8px); box-shadow: 0 30px 60px -12px rgba(37,99,235,.25); border-color: rgba(37,99,235,.3); }
    .premium-card:hover .premium-img { transform: scale(1.08); }
    .premium-img { transition: transform 1s cubic-bezier(.16,1,.3,1); }
    .premium-card:hover .premium-title { color: #2563eb; }

    .premium-fav { opacity: 0; transform: scale(.85); transition: all .25s ease; }
    .premium-card:hover .premium-fav { opacity: 1; transform: scale(1); }

    /* ═══════════════ CHIPS ═══════════════ */
    .chip-pill { transition: all .2s ease; white-space: nowrap; }
    .chip-pill.active { background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%); color: #fff; border-color: transparent; box-shadow: 0 4px 14px rgba(37,99,235,.35); }
    .chip-pill:not(.active):hover { border-color: #93c5fd; color: #1d4ed8; }

    .chips-scroll::-webkit-scrollbar { display: none; }
    .chips-scroll { scrollbar-width: none; }

    .search-glow:focus-within { border-color: #3b82f6; box-shadow: 0 8px 32px rgba(37,99,235,.15), 0 0 0 4px rgba(59,130,246,.08); }
        /* Multi-language heading spacing */
    .globe-heading { line-height: 1.4 !important; }
    .globe-heading span.inline-block { line-height: 1.4; }

</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- HERO                                                     --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden pt-12 md:pt-20 pb-12">
    <div class="max-w-7xl mx-auto px-6 md:px-10">
        <div class="text-center max-w-3xl mx-auto">

            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 border border-blue-100 mb-6">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <span class="text-xs font-semibold text-blue-700">
                    <strong>{{ number_format($services->total()) }}</strong>+ {{ __('messages.tourism_services') }} · {{ __('messages.verified_providers') }}
                </span>
            </div>

            <h1 class="globe-heading text-4xl md:text-6xl font-extrabold tracking-tight text-gray-900">
    {{ __('messages.discover_nepal_line1') }}<br class="hidden md:inline">
    <span class="mt-2 md:mt-3 inline-block">
        <span class="text-blue-600">{{ __('messages.discover_nepal_line2') }}</span> {{ __('messages.discover_nepal_line3') }}
    </span>
</h1>

            <p class="text-gray-600 text-lg md:text-xl mt-6 max-w-2xl mx-auto leading-relaxed">
                {{ __('messages.explore_hero_subtitle') }}
            </p>

            {{-- Search --}}
            <div class="mt-10 max-w-2xl mx-auto">
                <form method="GET" action="{{ route('public.services.index') }}" role="search" class="search-glow flex items-center gap-3 bg-white rounded-2xl border border-gray-200 shadow-lg px-5 py-2 transition-all">
                    <input type="hidden" name="category" value="{{ $categorySlug }}">
                    <i class="fas fa-search text-gray-400" aria-hidden="true"></i>
                    <input type="text" name="search" id="heroSearch"
                           value="{{ request('search') }}"
                           placeholder="{{ __('messages.search_placeholder') }}"
                           aria-label="{{ __('messages.search_label') }}"
                           class="flex-1 bg-transparent border-0 outline-none py-3 text-gray-800 placeholder-gray-400 focus:ring-0"
                           autocomplete="off">
                    @if(request('search'))
                        <a href="{{ route('public.services.index', ['category' => $categorySlug]) }}"
                           class="flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:bg-gray-100 hover:text-red-500 transition"
                           title="{{ __('messages.clear_search') }}"
                           aria-label="{{ __('messages.clear_search') }}">
                            <i class="fas fa-times text-sm" aria-hidden="true"></i>
                        </a>
                    @endif
                    <button type="submit"
                            aria-label="{{ __('messages.explore') }}"
                            class="hidden sm:inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition shadow-md">
                        {{ __('messages.explore') }} <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                    </button>
                </form>

                <div class="flex flex-wrap justify-center gap-2 mt-4">
                    <button type="button" class="quick-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-gray-200 text-xs text-gray-600 hover:border-blue-400 hover:text-blue-600 transition">🏔️ {{ __('messages.quick_ebc') }}</button>
                    <button type="button" class="quick-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-gray-200 text-xs text-gray-600 hover:border-blue-400 hover:text-blue-600 transition">🏞️ {{ __('messages.quick_abc') }}</button>
                    <button type="button" class="quick-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-gray-200 text-xs text-gray-600 hover:border-blue-400 hover:text-blue-600 transition">🪂 {{ __('messages.quick_paragliding') }}</button>
                    <button type="button" class="quick-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-gray-200 text-xs text-gray-600 hover:border-blue-400 hover:text-blue-600 transition">🏛️ {{ __('messages.quick_kathmandu') }}</button>
                    <button type="button" class="quick-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-gray-200 text-xs text-gray-600 hover:border-blue-400 hover:text-blue-600 transition">🐘 {{ __('messages.quick_chitwan') }}</button>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- 3D GLOBE                                                 --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<section class="bg-gradient-to-br from-blue-50 to-indigo-50 py-16 border-y border-blue-100">
    <div class="max-w-7xl mx-auto px-6 md:px-10">
        <div class="grid md:grid-cols-2 gap-12 items-center">

            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-blue-200 mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    <span class="text-xs font-semibold text-blue-700">{{ __('messages.live') }} · {{ number_format($services->total()) }}+ {{ __('messages.services_mapped') }}</span>
                </div>

                <h2 class="globe-heading text-3xl md:text-5xl font-extrabold tracking-tight text-gray-900">
    {{ __('messages.globe_title_line1') }}<br class="hidden md:inline">
    <span class="mt-2 md:mt-3 inline-block">
        {{ __('messages.globe_title_line2') }} <span class="text-blue-600">{{ __('messages.globe_title_highlight') }}</span>
    </span>
</h2>

                <p class="text-gray-600 text-lg mt-5 max-w-lg leading-relaxed">
                    {{ __('messages.globe_description') }}
                </p>

                <div class="flex gap-8 mt-8 pt-8 border-t border-blue-200">
                    <div>
                        <div class="text-3xl font-black text-gray-900">8,848<span class="text-blue-600 text-base">m</span></div>
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mt-1">{{ __('messages.everest') }}</div>
                    </div>
                    <div>
                        <div class="text-3xl font-black text-gray-900">7</div>
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mt-1">{{ __('messages.unesco_sites') }}</div>
                    </div>
                    <div>
                        <div class="text-3xl font-black text-gray-900">3</div>
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-semibold mt-1">{{ __('messages.base_camps') }}</div>
                    </div>
                </div>

                <div class="mt-8">
                    <button id="scrollToMap" type="button"
                            aria-label="{{ __('messages.explore_map') }}"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition shadow-md">
                        <i class="fas fa-map-marked-alt" aria-hidden="true"></i> {{ __('messages.explore_map') }}
                    </button>
                </div>
            </div>

            <div class="globe-wrap-premium">
                <div class="globe-glow-premium"></div>
                <div id="globeViz" role="img" aria-label="{{ __('messages.globe_aria') }}"></div>
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                    <button type="button" class="globe-btn active" id="globeAuto" title="{{ __('messages.toggle_rotation') }}" aria-label="{{ __('messages.toggle_rotation') }}"><i class="fas fa-sync-alt text-sm" aria-hidden="true"></i></button>
                    <button type="button" class="globe-btn" id="globeReset" title="{{ __('messages.reset_view') }}" aria-label="{{ __('messages.reset_view') }}"><i class="fas fa-globe text-sm" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- INTERACTIVE MAP                                          --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<section id="mapSection" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-6 md:px-10">
        <div class="flex flex-wrap justify-between items-end gap-4 mb-8">
            <div>
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider bg-blue-50 px-3 py-1 rounded-full">{{ __('messages.map_view') }}</span>
                <h2 class="text-3xl md:text-4xl font-bold mt-3 text-gray-900">{{ __('messages.nepal_at_glance') }}</h2>
                <p class="text-gray-500 mt-2">{{ __('messages.click_pin_hint') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="map-filter chip-pill active px-3 py-1.5 rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-600" data-map-filter="all">{{ __('messages.all') }}</button>
                <button type="button" class="map-filter chip-pill px-3 py-1.5 rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-600" data-map-filter="trek">🏔️ {{ __('messages.treks') }}</button>
                <button type="button" class="map-filter chip-pill px-3 py-1.5 rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-600" data-map-filter="tour">🏛️ {{ __('messages.tours') }}</button>
                <button type="button" class="map-filter chip-pill px-3 py-1.5 rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-600" data-map-filter="activity">🪂 {{ __('messages.activities') }}</button>
                <button type="button" class="map-filter chip-pill px-3 py-1.5 rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-600" data-map-filter="wildlife">🐘 {{ __('messages.wildlife') }}</button>
            </div>
        </div>

        <div class="relative rounded-2xl overflow-hidden shadow-xl border border-gray-100 bg-white">
            <div id="nepalMap" role="img" aria-label="{{ __('messages.map_aria') }}"></div>
            <div class="absolute bottom-4 left-4 z-[500] bg-white/95 backdrop-blur rounded-xl shadow-lg border border-gray-100 px-4 py-3 text-xs space-y-1.5">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span> {{ __('messages.trek') }}</div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-violet-600"></span> {{ __('messages.tour') }}</div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> {{ __('messages.activity') }}</div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span> {{ __('messages.pilgrimage') }}</div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> {{ __('messages.wildlife') }}</div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- STATS BAR                                                --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<section class="bg-gray-50 border-y border-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-6 md:px-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="text-3xl md:text-4xl font-black text-blue-600">{{ number_format($services->total()) }}</div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mt-1">{{ __('messages.tourism_services') }}</p>
            </div>
            <div class="text-center">
                <div class="text-3xl md:text-4xl font-black text-blue-600">{{ $categories->count() }}</div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mt-1">{{ __('messages.categories') }}</p>
            </div>
            <div class="text-center">
                <div class="text-3xl md:text-4xl font-black text-blue-600">726<span class="text-lg">+</span></div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mt-1">{{ __('messages.trusted_providers') }}</p>
            </div>
            <div class="text-center">
                <div class="text-3xl md:text-4xl font-black text-blue-600">0%</div>
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mt-1">{{ __('messages.zero_commission') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- FILTERS + SERVICES GRID                                  --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-6 md:px-10">

        <div class="flex flex-wrap justify-between items-end gap-4 mb-6">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900">
                    {{ __('messages.explore_destinations') }}
                    <span class="text-sm font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full ml-2">{{ number_format($services->total()) }} {{ __('messages.results') }}</span>
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <label for="sortSelect" class="sr-only">{{ __('messages.sort_by') }}</label>
                <select id="sortSelect" aria-label="{{ __('messages.sort_by') }}"
                        class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs font-semibold text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer hover:border-blue-400 transition">
                    <option value="popular" {{ request('sort') == 'popular' || !request('sort') ? 'selected' : '' }}>{{ __('messages.sort_popular') }}</option>
                    <option value="price-low" {{ request('sort') == 'price-low' ? 'selected' : '' }}>{{ __('messages.sort_price_low') }}</option>
                    <option value="price-high" {{ request('sort') == 'price-high' ? 'selected' : '' }}>{{ __('messages.sort_price_high') }}</option>
                    <option value="duration" {{ request('sort') == 'duration' ? 'selected' : '' }}>{{ __('messages.sort_duration') }}</option>
                    <option value="altitude" {{ request('sort') == 'altitude' ? 'selected' : '' }}>{{ __('messages.sort_altitude') }}</option>
                </select>
            </div>
        </div>

        {{-- Category chips --}}
        <div class="chips-scroll flex gap-2 overflow-x-auto pb-4 mb-6 border-b border-gray-100" role="tablist">
            <a href="{{ route('public.services.index', ['category' => 'all']) }}"
               data-category="all"
               role="tab"
               class="category-link chip-pill {{ $categorySlug == 'all' ? 'active' : '' }} px-4 py-2 rounded-full bg-white border border-gray-200 text-sm font-semibold text-gray-700 inline-flex items-center gap-2">
                ✨ {{ __('messages.all') }}
                <span class="text-xs opacity-70 font-normal">({{ number_format($totalCount) }})</span>
            </a>
            @foreach($categories as $cat)
                @php
                    $catIcon = '📍';
                    $slug = strtolower($cat->slug ?? '');
                    if (str_contains($slug, 'trek')) $catIcon = '🏔️';
                    elseif (str_contains($slug, 'tour')) $catIcon = '🏛️';
                    elseif (str_contains($slug, 'hotel')) $catIcon = '🏨';
                    elseif (str_contains($slug, 'guide')) $catIcon = '🧭';
                    elseif (str_contains($slug, 'transport')) $catIcon = '🚐';
                    elseif (str_contains($slug, 'activity')) $catIcon = '🪂';
                    elseif (str_contains($slug, 'experience')) $catIcon = '✨';

                    $count = $categoryCounts[$cat->id] ?? 0;
                @endphp
                <a href="{{ route('public.services.index', ['category' => $cat->slug]) }}"
                   data-category="{{ $cat->slug }}"
                   role="tab"
                   class="category-link chip-pill {{ $categorySlug == $cat->slug ? 'active' : '' }} px-4 py-2 rounded-full bg-white border border-gray-200 text-sm font-semibold text-gray-700 inline-flex items-center gap-2">
                    {{ $catIcon }} {{ $cat->name }}
                    <span class="text-xs opacity-70 font-normal">({{ number_format($count) }})</span>
                </a>
            @endforeach
        </div>

        {{-- Search + Filters --}}
        <form method="GET" action="{{ route('public.services.index') }}" class="mb-8 flex flex-wrap gap-3 items-center">
            <input type="hidden" name="category" value="{{ $categorySlug }}">
            <div class="flex-1 min-w-[220px] relative">
                <label for="filterSearch" class="sr-only">{{ __('messages.search_label') }}</label>
                <input type="text" name="search" id="filterSearch" value="{{ request('search') }}"
                       placeholder="{{ __('messages.search_services_placeholder') }}"
                       aria-label="{{ __('messages.search_label') }}"
                       class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if(request('search'))
                    <a href="{{ route('public.services.index', ['category' => $categorySlug]) }}"
                       class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition"
                       title="{{ __('messages.clear_search') }}"
                       aria-label="{{ __('messages.clear_search') }}">
                        <i class="fas fa-times-circle" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
            <label for="minPrice" class="sr-only">{{ __('messages.min_price') }}</label>
            <input type="number" name="min_price" id="minPrice" value="{{ request('min_price') }}"
                   placeholder="{{ __('messages.min_price') }}"
                   aria-label="{{ __('messages.min_price') }}"
                   class="w-28 px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            <label for="maxPrice" class="sr-only">{{ __('messages.max_price') }}</label>
            <input type="number" name="max_price" id="maxPrice" value="{{ request('max_price') }}"
                   placeholder="{{ __('messages.max_price') }}"
                   aria-label="{{ __('messages.max_price') }}"
                   class="w-28 px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            <label for="difficulty" class="sr-only">{{ __('messages.difficulty') }}</label>
            <select name="difficulty" id="difficulty" aria-label="{{ __('messages.difficulty') }}"
                    class="px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">{{ __('messages.difficulty') }}</option>
                <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>{{ __('messages.easy') }}</option>
                <option value="moderate" {{ request('difficulty') == 'moderate' ? 'selected' : '' }}>{{ __('messages.moderate') }}</option>
                <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>{{ __('messages.hard') }}</option>
            </select>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl transition font-semibold text-sm">
                <i class="fas fa-search" aria-hidden="true"></i> {{ __('messages.filter') }}
            </button>
            <a href="{{ route('public.services.index', ['category' => $categorySlug]) }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-xl transition font-semibold text-sm">
                {{ __('messages.reset') }}
            </a>
        </form>

        @php
            // Priority sort — only when no search + All category
            if (!request('search') && !request('sort') && $categorySlug === 'all') {
                $items = $services->getCollection();
                $priorityMap = [
                    'trek' => 1, 'tour' => 2, 'activity' => 3,
                    'experience' => 4, 'hotel' => 5, 'guide' => 6, 'transport' => 7,
                ];
                $popularTreks = [
                    'everest base camp' => 0, 'annapurna circuit' => 0, 'manaslu circuit' => 0,
                    'kanchenjunga' => 0, 'langtang' => 1, 'gokyo' => 1, 'three passes' => 1,
                ];
                $sorted = $items->sortBy(function($s) use ($priorityMap, $popularTreks) {
                    $catSlug = strtolower($s->category->slug ?? '');
                    $nameLower = strtolower($s->name);
                    $popularBoost = 10;
                    foreach ($popularTreks as $kw => $boost) {
                        if (str_contains($nameLower, $kw)) { $popularBoost = $boost; break; }
                    }
                    $catPriority = 999;
                    foreach ($priorityMap as $key => $weight) {
                        if (str_contains($catSlug, $key)) { $catPriority = $weight; break; }
                    }
                    return ($popularBoost * 100) + $catPriority;
                })->values();
                $services->setCollection($sorted);
            }
        @endphp

        {{-- Services Grid --}}
        <div id="servicesWrapper">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($services as $index => $service)
                    @php
                        $currencyService = app(\App\Services\CurrencyService::class);
                        $displayCurrency = $currencyService->getDisplayCurrency();
                        $baseCurrency = $service->currency ?? 'USD';
                        $displayPrice = $currencyService->convert($service->price, $baseCurrency, $displayCurrency);
                        $formattedPrice = $currencyService->format($displayPrice, $displayCurrency);

                        $catSlug = strtolower($service->category->slug ?? '');
                        $catIcon = '📍';
                        $catBadge = 'bg-blue-50 text-blue-700';
                        if (str_contains($catSlug, 'trek')) { $catIcon = '🏔️'; $catBadge = 'bg-blue-50 text-blue-700'; }
                        elseif (str_contains($catSlug, 'tour')) { $catIcon = '🏛️'; $catBadge = 'bg-violet-50 text-violet-700'; }
                        elseif (str_contains($catSlug, 'hotel')) { $catIcon = '🏨'; $catBadge = 'bg-amber-50 text-amber-700'; }
                        elseif (str_contains($catSlug, 'guide')) { $catIcon = '🧭'; $catBadge = 'bg-emerald-50 text-emerald-700'; }
                        elseif (str_contains($catSlug, 'transport')) { $catIcon = '🚐'; $catBadge = 'bg-slate-50 text-slate-700'; }
                        elseif (str_contains($catSlug, 'activity')) { $catIcon = '🪂'; $catBadge = 'bg-orange-50 text-orange-700'; }
                        elseif (str_contains($catSlug, 'experience')) { $catIcon = '✨'; $catBadge = 'bg-pink-50 text-pink-700'; }

                        $difficulty = $service->trekDetail->difficulty ?? null;
                        $diffClass = match($difficulty) {
                            'easy' => 'bg-green-50 text-green-700 border-green-100',
                            'hard' => 'bg-red-50 text-red-700 border-red-100',
                            default => 'bg-amber-50 text-amber-700 border-amber-100',
                        };
                        $isFeatured = ($index === 0 && !request('search'));
                    @endphp

                    <article class="premium-card group bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-2xl transition border border-gray-100 cursor-pointer flex flex-col {{ $isFeatured ? 'lg:col-span-2' : '' }}"
                             onclick="window.location='{{ route('public.services.show', $service->slug) }}'">
                        <div class="relative {{ $isFeatured ? 'h-72' : 'h-52' }} bg-gradient-to-br from-blue-400 to-indigo-500 overflow-hidden">
                            @if($service->cover_image)
                                <img src="{{ asset('storage/' . $service->cover_image) }}"
                                     alt="{{ $service->name }}"
                                     loading="lazy"
                                     class="premium-img w-full h-full object-cover">
                            @else
                                <img src="{{ asset('images/logo.png') }}"
                                     alt="{{ $service->name }}"
                                     loading="lazy"
                                     class="premium-img w-full h-full object-contain p-8 opacity-70">
                            @endif

                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                            <div class="absolute top-3 left-3 flex gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full {{ $catBadge }} backdrop-blur text-[11px] font-bold shadow-sm">
                                    {{ $catIcon }} {{ strtoupper($service->category->name ?? 'SERVICE') }}
                                </span>
                                @if($difficulty)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full border {{ $diffClass }} backdrop-blur text-[11px] font-bold shadow-sm">
                                        {{ ucfirst($difficulty) }}
                                    </span>
                                @endif
                            </div>

                            <button type="button" class="premium-fav absolute top-3 right-3 w-9 h-9 rounded-full bg-white/95 backdrop-blur border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-red-500 hover:text-white transition"
                                    aria-label="{{ __('messages.add_to_favorites') }}"
                                    onclick="event.stopPropagation(); this.classList.toggle('bg-red-500'); this.classList.toggle('text-white');">
                                <i class="fas fa-heart text-sm" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="p-5 flex-1 flex flex-col">
                            <div class="flex items-center gap-1.5 text-[11px] uppercase tracking-wider text-gray-500 font-semibold mb-2">
                                <i class="fas fa-map-marker-alt text-blue-500" aria-hidden="true"></i>
                                {{ $service->provider->name ?? __('messages.travelai_partner') }}
                            </div>

                            <h3 class="premium-title text-lg font-bold text-gray-900 leading-snug mb-2 line-clamp-2 transition">
                                {{ $service->name }}
                            </h3>

                            @if($service->description)
                                <p class="text-sm text-gray-500 line-clamp-2 leading-relaxed">
                                    {{ Str::limit($service->description, 100) }}
                                </p>
                            @endif

                            <div class="flex flex-wrap gap-3 mt-4 pt-4 border-t border-gray-100 text-xs text-gray-500">
                                <span class="flex items-center gap-1.5">
                                    <i class="far fa-calendar-alt" aria-hidden="true"></i>
                                    <strong class="text-gray-700">{{ $service->trekDetail->duration_days ?? '—' }}</strong>
                                    {{ __('messages.days') }}
                                </span>
                                @if($service->trekDetail && $service->trekDetail->max_altitude)
                                    <span class="flex items-center gap-1.5">
                                        <i class="fas fa-mountain" aria-hidden="true"></i>
                                        <strong class="text-gray-700">{{ number_format($service->trekDetail->max_altitude) }}</strong> m
                                    </span>
                                @endif
                                <span class="flex items-center gap-1.5 ml-auto">
                                    <i class="fas fa-star text-yellow-400" aria-hidden="true"></i>
                                    <strong class="text-gray-700">4.8</strong>
                                </span>
                            </div>
                        </div>

                        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                            <div>
                                <div class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold">{{ __('messages.from') }}</div>
                                <div class="text-base font-bold text-gray-900">
                                    {{ $formattedPrice }}
                                    <span class="text-xs text-gray-500 font-medium">{{ __('messages.per_person') }}</span>
                                </div>
                            </div>
                            <div class="w-9 h-9 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-600 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition">
                                <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-16">
                        <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-4">
                            <i class="fas fa-search text-2xl" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-1">{{ __('messages.no_services_found') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('messages.try_adjust_filters') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Load More --}}
        @if($services->hasMorePages())
        <div class="mt-12 text-center" id="loadMoreWrap">
            <button type="button" id="loadMoreBtn"
                    data-next-page="{{ $services->currentPage() + 1 }}"
                    aria-label="{{ __('messages.load_more') }}"
                    class="inline-flex items-center gap-2 bg-white hover:bg-blue-50 text-gray-800 border-2 border-gray-200 hover:border-blue-500 font-semibold px-8 py-3 rounded-xl transition shadow-sm hover:shadow-md">
                {{ __('messages.load_more_destinations') }}
                <i class="fas fa-arrow-down text-xs" aria-hidden="true"></i>
            </button>
        </div>
        @endif

        <div class="mt-12 justify-center hidden">
            {{ $services->appends(request()->query())->links() }}
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- CTA                                                      --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-6 md:px-10">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-8 md:p-12 text-center text-white shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-80 h-80 bg-white opacity-5 rounded-full blur-3xl"></div>
            <div class="relative">
                <h2 class="text-3xl md:text-4xl font-bold mb-3">
                    {{ __('messages.cta_title') }}
                </h2>
                <p class="text-blue-100 text-lg max-w-2xl mx-auto">
                    {{ __('messages.cta_subtitle') }}
                </p>
                <div class="flex flex-wrap justify-center gap-4 mt-6">
                    <a href="{{ url('/') }}#itineraryForm" class="inline-flex items-center gap-2 bg-white text-blue-600 hover:bg-gray-100 font-semibold px-8 py-3 rounded-xl transition shadow-lg">
                        <i class="fas fa-magic" aria-hidden="true"></i> {{ __('messages.try_ai_planner') }}
                    </a>
                    <a href="{{ url('/how-it-works') }}" class="inline-flex items-center gap-2 bg-transparent border-2 border-white text-white hover:bg-white/10 font-semibold px-8 py-3 rounded-xl transition">
                        {{ __('messages.how_it_works') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
// ═══════════════════════════════════════════════════════════
// GLOBE
// ═══════════════════════════════════════════════════════════
function initGlobe() {
    const el = document.getElementById('globeViz');
    if (!el || typeof Globe === 'undefined') return;

    const globe = Globe()(el)
        .globeImageUrl('//unpkg.com/three-globe/example/img/earth-blue-marble.jpg')
        .bumpImageUrl('//unpkg.com/three-globe/example/img/earth-topology.png')
        .backgroundColor('rgba(0,0,0,0)')
        .atmosphereColor('#3b82f6')
        .atmosphereAltitude(0.18)
        .width(el.clientWidth).height(el.clientHeight);

    const NEPAL = { lat: 28.3949, lng: 84.1240 };
    const pins = [
        { lat: 27.9881, lng: 86.9250, name: 'Everest Base Camp', category: 'trek' },
        { lat: 28.5965, lng: 84.0178, name: 'Annapurna Circuit', category: 'trek' },
        { lat: 28.2446, lng: 83.9453, name: 'Pokhara Paragliding', category: 'activity' },
        { lat: 27.5291, lng: 84.3542, name: 'Chitwan Safari', category: 'wildlife' },
        { lat: 27.7172, lng: 85.3240, name: 'Kathmandu City Tour', category: 'tour' },
        { lat: 27.4833, lng: 83.2833, name: 'Lumbini Pilgrimage', category: 'pilgrimage' },
        { lat: 28.6667, lng: 84.1250, name: 'Manaslu Circuit', category: 'trek' },
        { lat: 28.0867, lng: 85.3583, name: 'Langtang Valley', category: 'trek' },
        { lat: 28.8177, lng: 83.8849, name: 'Muktinath Temple', category: 'pilgrimage' },
        { lat: 27.7000, lng: 88.1333, name: 'Kanchenjunga Circuit', category: 'trek' }
    ];
    const colorMap = { trek:'#2563eb', tour:'#7c3aed', activity:'#f59e0b', pilgrimage:'#ec4899', wildlife:'#10b981' };

    globe.pointsData(pins)
        .pointLat('lat').pointLng('lng')
        .pointColor(d => colorMap[d.category] || '#2563eb')
        .pointAltitude(0.012).pointRadius(0.42)
        .pointLabel(d => '<div style="background:#fff;padding:10px 14px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.15);font-family:Inter,sans-serif;font-size:12px;font-weight:600;color:#111827;">' + d.name + '</div>')
        .onPointClick(function() { const s = document.getElementById('mapSection'); if (s) s.scrollIntoView({ behavior: 'smooth' }); });

    globe.ringsData(pins).ringLat('lat').ringLng('lng')
        .ringColor(function() { return function(t) { return 'rgba(37,99,235,' + (1 - t) + ')'; }; })
        .ringMaxRadius(3.5).ringPropagationSpeed(2.2).ringRepeatPeriod(1600);

    globe.labelsData([
        { lat: 27.9881, lng: 86.9250, text: 'Everest', size: 0.85 },
        { lat: 28.2096, lng: 83.9856, text: 'Pokhara', size: 0.75 },
        { lat: 27.7172, lng: 85.3240, text: 'Kathmandu', size: 0.85 }
    ]).labelLat('lat').labelLng('lng').labelText('text').labelSize('size').labelDotRadius(0)
      .labelColor(function() { return 'rgba(37,99,235,.95)'; }).labelResolution(3);

    globe.pointOfView({ lat: NEPAL.lat, lng: NEPAL.lng, altitude: 1.85 }, 0);

    let autoRotate = true;
    const controls = globe.controls();
    controls.autoRotate = true; controls.autoRotateSpeed = 0.4;
    controls.enableZoom = true; controls.enablePan = false;
    controls.minDistance = 180; controls.maxDistance = 520;

    const autoBtn = document.getElementById('globeAuto');
    if (autoBtn) autoBtn.addEventListener('click', function() {
        autoRotate = !autoRotate;
        controls.autoRotate = autoRotate;
        this.classList.toggle('active', autoRotate);
    });
    const resetBtn = document.getElementById('globeReset');
    if (resetBtn) resetBtn.addEventListener('click', function() {
        globe.pointOfView({ lat: NEPAL.lat, lng: NEPAL.lng, altitude: 1.85 }, 900);
    });

    new ResizeObserver(function() { globe.width(el.clientWidth).height(el.clientHeight); }).observe(el);
}

// ═══════════════════════════════════════════════════════════
// MAP
// ═══════════════════════════════════════════════════════════
function initMap() {
    const el = document.getElementById('nepalMap');
    if (!el || typeof L === 'undefined') return;

    const map = L.map('nepalMap', { center: [28.3949, 84.1240], zoom: 7, zoomControl: true, scrollWheelZoom: false });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CartoDB', subdomains: 'abcd', maxZoom: 19
    }).addTo(map);

    const mapPins = [
        { lat: 27.9881, lng: 86.9250, name: 'Everest Base Camp', category: 'trek', days: 14, price: 1180, slug: 'everest-base-camp' },
        { lat: 28.5965, lng: 84.0178, name: 'Annapurna Circuit', category: 'trek', days: 15, price: 980, slug: 'annapurna-circuit' },
        { lat: 28.2446, lng: 83.9453, name: 'Pokhara Paragliding', category: 'activity', days: 1, price: 95, slug: 'pokhara-paragliding' },
        { lat: 27.5291, lng: 84.3542, name: 'Chitwan Safari', category: 'wildlife', days: 3, price: 340, slug: 'chitwan-safari' },
        { lat: 27.7172, lng: 85.3240, name: 'Kathmandu City Tour', category: 'tour', days: 2, price: 185, slug: 'kathmandu-city-tour' },
        { lat: 27.4833, lng: 83.2833, name: 'Lumbini Pilgrimage', category: 'pilgrimage', days: 1, price: 85, slug: 'lumbini-mayadevi' },
        { lat: 28.6667, lng: 84.1250, name: 'Manaslu Circuit', category: 'trek', days: 14, price: 1240, slug: 'manaslu-circuit' },
        { lat: 28.0867, lng: 85.3583, name: 'Langtang Valley', category: 'trek', days: 7, price: 620, slug: 'langtang-valley' },
        { lat: 28.8177, lng: 83.8849, name: 'Muktinath Temple', category: 'pilgrimage', days: 2, price: 245, slug: 'muktinath-temple-tour' },
        { lat: 28.2750, lng: 83.7000, name: 'Kusma Bungee', category: 'activity', days: 1, price: 120, slug: 'kusma-bungee' },
        { lat: 29.1833, lng: 83.9667, name: 'Upper Mustang', category: 'tour', days: 12, price: 1580, slug: 'upper-mustang' },
        { lat: 27.7000, lng: 88.1333, name: 'Kanchenjunga Circuit', category: 'trek', days: 23, price: 1960, slug: 'kanchenjunga-circuit' },
        { lat: 27.7992, lng: 84.4667, name: 'Bandipur', category: 'tour', days: 2, price: 145, slug: 'bandipur' },
        { lat: 28.8056, lng: 83.7333, name: 'Kali Gandaki Rafting', category: 'activity', days: 2, price: 175, slug: 'kali-gandaki-rafting' },
        { lat: 28.1800, lng: 85.3833, name: 'Gosaikunda', category: 'trek', days: 9, price: 760, slug: 'gosaikunda' },
        { lat: 28.5017, lng: 84.3483, name: 'Tilicho Lake', category: 'trek', days: 11, price: 890, slug: 'tilicho-lake' }
    ];

    const iconMap = { trek:'🏔️', tour:'🏛️', activity:'🪂', pilgrimage:'🛕', wildlife:'🐘' };
    const markers = [];
    const baseUrl = '{{ url("/services") }}';

    mapPins.forEach(function(pin) {
        const icon = L.divIcon({
            className: 'custom-pin-wrapper',
            html: '<div class="custom-pin ' + pin.category + '">' + (iconMap[pin.category] || '📍') + '</div>',
            iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -16]
        });
        const popup = '<div style="font-weight:700;font-size:14px;color:#111827;margin-bottom:6px;">' + pin.name + '</div>' +
            '<div style="display:flex;gap:12px;font-size:11.5px;color:#6b7280;margin-bottom:10px;">' +
            '<span>⏱ ' + pin.days + ' ' + (pin.days === 1 ? 'day' : 'days') + '</span>' +
            '<span>💵 $' + pin.price + '</span></div>' +
            '<a href="' + baseUrl + '/' + pin.slug + '" style="display:inline-block;background:#2563eb;color:#fff;font-size:11.5px;font-weight:600;padding:6px 12px;border-radius:8px;text-decoration:none;">View Details →</a>';

        const marker = L.marker([pin.lat, pin.lng], { icon: icon }).addTo(map).bindPopup(popup);
        marker.category = pin.category;
        markers.push(marker);
    });

    document.querySelectorAll('.map-filter').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.map-filter').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            const filter = btn.dataset.mapFilter;
            markers.forEach(function(m) {
                if (filter === 'all' || m.category === filter) { if (!map.hasLayer(m)) m.addTo(map); }
                else if (map.hasLayer(m)) { map.removeLayer(m); }
            });
        });
    });

    const scrollBtn = document.getElementById('scrollToMap');
    if (scrollBtn) scrollBtn.addEventListener('click', function() {
        const s = document.getElementById('mapSection');
        if (s) s.scrollIntoView({ behavior: 'smooth' });
    });

    el.addEventListener('click', function() { map.scrollWheelZoom.enable(); });
    el.addEventListener('mouseleave', function() { map.scrollWheelZoom.disable(); });
}

// ═══════════════════════════════════════════════════════════
// INIT + HANDLERS
// ═══════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {
    initGlobe();
    initMap();

    // Quick chips → submit hero form
    document.querySelectorAll('.quick-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
            const text = this.textContent.replace(/[^\w\s]/g, '').trim();
            const heroForm = document.querySelector('form.search-glow');
            if (!heroForm) return;
            const input = heroForm.querySelector('input[name="search"]');
            if (input) input.value = text;
            heroForm.submit();
        });
    });

    // ══════════════════════════════════════════════════════════
    // SORT DROPDOWN (FIXED)
    // ══════════════════════════════════════════════════════════
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', this.value);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    // ══════════════════════════════════════════════════════════
    // CATEGORY AJAX SWITCHING
    // ══════════════════════════════════════════════════════════
    document.querySelectorAll('.category-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');

            document.querySelectorAll('.category-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            const wrapper = document.getElementById('servicesWrapper');
            if (wrapper) wrapper.style.opacity = '0.4';

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newWrapper = doc.getElementById('servicesWrapper');
                    if (newWrapper && wrapper) {
                        wrapper.innerHTML = newWrapper.innerHTML;
                    }

                    window.history.pushState({}, '', url);
                    if (wrapper) wrapper.style.opacity = '1';
                })
                .catch(err => {
                    console.error('AJAX failed, falling back to full reload');
                    if (wrapper) wrapper.style.opacity = '1';
                    window.location.href = url;
                });
        });
    });

    // Back/forward
    window.addEventListener('popstate', function() {
        window.location.reload();
    });

    // ══════════════════════════════════════════════════════════
    // LOAD MORE
    // ══════════════════════════════════════════════════════════
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('#loadMoreBtn');
        if (!btn) return;

        const nextPage = btn.dataset.nextPage;
        if (!nextPage) return;

        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

        const params = new URLSearchParams(window.location.search);
        params.set('page', nextPage);
        const url = window.location.pathname + '?' + params.toString();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const currentGrid = document.querySelector('#servicesWrapper .grid');
                const newGrid = doc.querySelector('#servicesWrapper .grid');

                if (currentGrid && newGrid) {
                    newGrid.querySelectorAll('article').forEach(a => currentGrid.appendChild(a));
                }

                const newBtn = doc.querySelector('#loadMoreBtn');
                const wrapper = document.getElementById('loadMoreWrap');

                if (newBtn && newBtn.dataset.nextPage && wrapper) {
                    btn.dataset.nextPage = newBtn.dataset.nextPage;
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                } else if (wrapper) {
                    wrapper.innerHTML = '<p class="text-sm text-gray-500">✓ ' + '{{ __('messages.all_loaded') }}' + '</p>';
                }
            })
            .catch(err => {
                console.error('Load more failed:', err);
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            });
    });
});
</script>
@endpush
