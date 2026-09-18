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

{{-- ========== Leaflet + Globe.gl + TopoJSON (async) ========== --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
<script src="//unpkg.com/globe.gl" defer></script>
<script src="//unpkg.com/three" defer></script>
<script src="https://unpkg.com/topojson-client@3" defer></script>

<style>
    /* ═══════════════ GLOBE (Responsive) ═══════════════ */
        .globe-wrap-premium {
        position: relative;
        width: 100%;
        height: clamp(300px, 50vw, 600px);
        max-width: min(600px, 100%);
        margin: 0 auto;
        overflow: hidden;
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

    /* ═══════════════ DISTRICT BOUNDARY LAYER (GLOBE-02) ═══════════════ */
    .leaflet-districts-pane {
        /* z-index set in JS (350) - below markers */
    }
    .district-tooltip {
        background: #fff !important;
        border: 1px solid rgba(0,0,0,.08) !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,.1) !important;
        padding: 6px 10px !important;
    }
    
    /* ═══════════════ DISTRICT PANEL (GLOBE-04) ═══════════════ */
    .district-panel {
        transform: translateY(100%);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .district-panel.is-open {
        transform: translateY(0);
    }
    @media (min-width: 768px) {
        .district-panel {
            transform: translateX(100%);
        }
        .district-panel.is-open {
            transform: translateX(0);
        }
    }

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
            {{-- GLOBE-06: Route selector — single route visualization --}}
            <div class="mt-2 mb-4">
                <label for="routeSelector" class="text-xs font-semibold text-gray-500 uppercase tracking-wider">🗺️ Explore a Route</label>
                <select id="routeSelector" class="mt-2 w-full md:w-1/2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700">
                    <option value="">— Select a route —</option>
                </select>
                <div id="routeMeta" class="mt-3 hidden rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700"></div>
                <div id="routeError" class="mt-2 hidden text-xs text-red-600"></div>
            </div>
            <div id="nepalMap" role="img" aria-label="{{ __('messages.map_aria') }}"></div>
            <div class="absolute bottom-4 left-4 z-[500] bg-white/95 backdrop-blur rounded-xl shadow-lg border border-gray-100 px-4 py-3 text-xs space-y-1.5">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span> {{ __('messages.trek') }}</div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-violet-600"></span> {{ __('messages.tour') }}</div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> {{ __('messages.activity') }}</div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span> {{ __('messages.pilgrimage') }}</div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> {{ __('messages.wildlife') }}</div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full" style="background-color: #991b1b;"></span> Cities</div>
                        </div>
            {{-- GLOBE-02: Boundary attribution (CC BY 4.0) --}}
            <div class="absolute bottom-4 right-4 z-[500] bg-white/95 backdrop-blur rounded-lg border border-gray-200 shadow-md px-3 py-1.5 text-[10px] text-gray-600 max-w-[240px] leading-tight">
                Boundaries:
                                <a href="https://localboundries.oknp.org" target="_blank" rel="noopener"
                   class="text-blue-600 hover:underline">Open Knowledge Nepal</a>
                (CC BY 4.0)
            </div>
                       {{-- GLOBE-03 v2: Hint badge (top-right, auto-hide at zoom >= 9) --}}
            <div id="waypointHint" class="absolute top-4 right-4 z-[500] bg-white/95 backdrop-blur rounded-lg border border-gray-200 shadow-md px-3 py-2 text-xs text-gray-700 font-medium leading-tight max-w-[240px] transition-opacity duration-300">
                📍 <span id="waypointCountHint">8</span> mapped cities · zoom in to explore
            </div>
            {{-- GLOBE-04: District information panel --}}
            <aside id="districtPanel" class="district-panel absolute z-[600] bg-white shadow-2xl border-gray-200 inset-x-0 bottom-0 max-h-[60vh] rounded-t-2xl border-t overflow-y-auto md:inset-x-auto md:right-0 md:top-0 md:bottom-0 md:w-80 md:max-h-full md:rounded-none md:rounded-l-2xl md:border-t-0 md:border-l">
                <div class="sticky top-0 bg-white border-b border-gray-100 px-4 py-3 flex justify-between items-start z-10">
                    <div class="min-w-0 flex-1">
                        <h3 id="districtPanelName" class="font-bold text-gray-900 text-base leading-tight truncate">—</h3>
                        <p id="districtPanelProvince" class="text-xs text-gray-500 mt-0.5 truncate">—</p>
                    </div>
                    <button id="districtPanelClose" type="button" class="flex-shrink-0 ml-2 w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-500 transition" aria-label="Close panel">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4">
                    <div id="districtPanelSummary" class="text-xs text-gray-500 mb-3"></div>
                    <div id="districtPanelList" class="space-y-2"></div>
                </div>
            </aside>
        </div>
        {{-- GLOBE-03 v2: Mapped Cities discovery chips --}}
        <div class="mt-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">📍 Mapped Cities</span>
                <span id="mappedCitiesCount" class="text-[10px] text-gray-400"></span>
            </div>
            <div id="mappedCitiesChips" class="chips-scroll flex gap-2 overflow-x-auto pb-1"></div>
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
// GLOBE-03: Shared map data loader (single fetch)
// ═══════════════════════════════════════════════════════════
var __mapDataPromise = null;
var __allWaypointsCache = null;
// GLOBE-06: Route selector state
var __nepalMapInstance = null;
var __globeInstance = null;
var __selectedRouteSlug = null;
var __leafletRouteLayer = null;
var __globeRouteAccessorsSet = false;
var __routeRequestToken = 0;
function loadMapData() {
    if (!__mapDataPromise) {
        __mapDataPromise = fetch('/api/map/init')
            .then(function(r) { return r.json(); })
            .then(function(json) {
                if (json && json.data) {
                    __allWaypointsCache = json.data.waypoints || [];
                    return json.data;
                }
                return null;
            })
            .catch(function(err) { console.warn('Map API fetch failed:', err); return null; });
    }
    return __mapDataPromise;
}

// GLOBE-03 D4: Dedup city waypoints by rounded coordinate (4 decimals)
// Does NOT mutate source. Aggregates names for popup.
function groupCityWaypoints(waypoints) {
    if (!Array.isArray(waypoints)) return [];
    var groups = {};
    waypoints.forEach(function (w) {
        if (!w || w.type !== 'city') return;
        var lat = Number(w.lat);
        var lng = Number(w.lng);
        if (!isFinite(lat) || !isFinite(lng)) return;
        var key = lat.toFixed(4) + ',' + lng.toFixed(4);
        if (!groups[key]) {
            groups[key] = {
                lat: w.lat,
                lng: w.lng,
                names: [],
                types: {},
                altitudes: [],
                count: 0,
            };
        }
        if (groups[key].names.indexOf(w.name) === -1) {
            groups[key].names.push(w.name);
        }
        groups[key].types[w.type] = true;
        var alt = w.altitude;
        if (alt !== null && alt !== undefined && alt !== '' && groups[key].altitudes.indexOf(alt) === -1) {
            groups[key].altitudes.push(alt);
        }
        groups[key].count++;
    });
    var result = [];
    for (var k in groups) {
        if (!groups.hasOwnProperty(k)) continue;
        groups[k].typeList = Object.keys(groups[k].types);
        result.push(groups[k]);
    }
    return result;
}

// GLOBE-03 v2: Build Mapped Cities chips below the map
function buildCityChips(cityGroups, map) {
    var container = document.getElementById('mappedCitiesChips');
    var countLabel = document.getElementById('mappedCitiesCount');
    var hintCount = document.getElementById('waypointCountHint');
    if (!container) return;

    // Sort by count descending (largest first)
    var sorted = cityGroups.slice().sort(function (a, b) { return b.count - a.count; });

    container.innerHTML = '';
    sorted.forEach(function (g) {
        var chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'chip-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-700 hover:border-red-400 hover:text-red-700 hover:bg-red-50 transition whitespace-nowrap';
        chip.setAttribute('data-city', g.names[0]);
        chip.setAttribute('data-count', g.count);

        chip.innerHTML =
            '<span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: #991b1b;"></span>' +
            '<span>' + g.names[0] + '</span>' +
            '<span class="text-gray-400 font-normal">(' + g.count + ')</span>';

        chip.addEventListener('click', function () {
            map.flyTo([g.lat, g.lng], 11, { duration: 1.2 });
            if (g._marker) {
                setTimeout(function () { g._marker.openPopup(); }, 1300);
            }
        });

        container.appendChild(chip);
    });

    if (countLabel) countLabel.textContent = '(' + cityGroups.length + ')';
    if (hintCount) hintCount.textContent = cityGroups.length;
}

// ═══════════════════════════════════════════════════════════
// GLOBE-04: District Interaction Panel
// ═══════════════════════════════════════════════════════════

// ---- Shared constants (moved from initMap for panel access) ----
function titleCaseDistrict(s) {
    return String(s).toLowerCase().replace(/\b\w/g, function (c) { return c.toUpperCase(); });
}

var districtBaseStyle = {
    fillColor: '#2563eb',
    fillOpacity: 0.04,
    color: '#2563eb',
    weight: 1,
    opacity: 0.35,
};

var districtHoverStyle = {
    fillOpacity: 0.15,
    weight: 2,
    opacity: 0.85,
};

var districtSelectedStyle = {
    fillColor: '#991b1b',
    fillOpacity: 0.18,
    color: '#991b1b',
    weight: 2,
    opacity: 0.95
};

var __selectedDistrictLayer = null;

// ---- District → state mapping (explicit, no fuzzy) ----
var DISTRICT_STATE_VARIANTS = {
    'CHITAWAN': 'Chitwan',
    'DHANUSHA': 'Dhanusa',
    'KAPILBASTU': 'Kapilavastu',
    'KABHREPALANCHOK': 'Kavrepalanchok'
};

var AMBIGUOUS_STATES = ['Bagmati', 'Gandaki', 'Lumbini', 'Rolwaling'];

function normalizeStateName(s) {
    return String(s || '').toLowerCase().replace(/[^a-z0-9]+/g, '');
}

function findWaypointsForDistrict(districtName, allWaypoints) {
    var lookupName = DISTRICT_STATE_VARIANTS[districtName] || districtName;

    for (var i = 0; i < AMBIGUOUS_STATES.length; i++) {
        if (normalizeStateName(AMBIGUOUS_STATES[i]) === normalizeStateName(lookupName)) {
            return { status: 'ambiguous', waypoints: [] };
        }
    }

    var normalized = normalizeStateName(lookupName);
    var matches = (allWaypoints || []).filter(function (w) {
        if (!w || !w.state) return false;
        return normalizeStateName(w.state) === normalized;
    });

    return { status: matches.length > 0 ? 'mapped' : 'empty', waypoints: matches };
}

function escapeHtmlSafe(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

function renderDistrictPanelContent(result) {
    var summary = document.getElementById('districtPanelSummary');
    var list = document.getElementById('districtPanelList');
    if (!summary || !list) return;

    if (result.status === 'ambiguous') {
        summary.textContent = '';
        list.innerHTML = '<p class="text-xs text-gray-500 italic">District-level waypoint data is not currently mapped for this district.</p>';
        return;
    }

    if (result.status === 'empty') {
        summary.textContent = '';
        list.innerHTML = '<p class="text-xs text-gray-500 italic">No waypoint data mapped to this district yet.</p>';
        return;
    }

    var n = result.waypoints.length;
    summary.innerHTML = '<span class="font-semibold text-gray-700">' + n + '</span> waypoint' + (n === 1 ? '' : 's') + ' mapped';

    var html = '';
    result.waypoints.forEach(function (w) {
        var alt = (w.altitude !== null && w.altitude !== undefined && w.altitude !== '') ? (w.altitude + ' m') : '—';
        html += '<div class="border border-gray-100 rounded-lg p-2.5 hover:border-gray-300 transition">' +
            '<div class="font-semibold text-sm text-gray-900 truncate">' + escapeHtmlSafe(w.name || 'Unnamed') + '</div>' +
            '<div class="flex items-center gap-3 mt-1 text-xs text-gray-500">' +
                '<span class="capitalize">' + escapeHtmlSafe(w.type || 'unknown') + '</span>' +
                '<span>Alt: ' + escapeHtmlSafe(alt) + '</span>' +
            '</div>' +
        '</div>';
    });
    list.innerHTML = html;
}

function openDistrictPanel(props, layer, map) {
    var panel = document.getElementById('districtPanel');
    if (!panel) return;

    var districtName = (props.DISTRICT || '').toUpperCase();
    var provinceName = props.PR_NAME || '';
    var displayName = titleCaseDistrict(props.DISTRICT || '');

    document.getElementById('districtPanelName').textContent = displayName;
    document.getElementById('districtPanelProvince').textContent = provinceName;
    document.getElementById('districtPanelSummary').textContent = 'Loading...';
    document.getElementById('districtPanelList').innerHTML = '';

    panel.classList.add('is-open');

    if (__selectedDistrictLayer && __selectedDistrictLayer !== layer) {
        __selectedDistrictLayer.setStyle(districtBaseStyle);
    }
    layer.setStyle(districtSelectedStyle);
    __selectedDistrictLayer = layer;

    if (map && typeof layer.getBounds === 'function') {
        try {
            map.fitBounds(layer.getBounds(), { padding: [30, 30], maxZoom: 10 });
        } catch (e) { /* ignore */ }
    }

    loadMapData().then(function () {
        var allWaypoints = __allWaypointsCache || [];
        var result = findWaypointsForDistrict(districtName, allWaypoints);
        renderDistrictPanelContent(result);
    });
}

function closeDistrictPanel() {
    var panel = document.getElementById('districtPanel');
    if (panel) panel.classList.remove('is-open');
    if (__selectedDistrictLayer) {
        __selectedDistrictLayer.setStyle(districtBaseStyle);
        __selectedDistrictLayer = null;
    }
}
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
    __globeInstance = globe;
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

        // GLOBE-03: Hero pins + city waypoints (H1) combined into pointsData
    loadMapData().then(function (mapData) {
                // GLOBE-03 D4: Use grouped city waypoints
        var cityGroups = [];
        if (mapData && Array.isArray(mapData.waypoints)) {
            cityGroups = groupCityWaypoints(mapData.waypoints);
        }
        var cityPoints = cityGroups.map(function (g) {
            return {
                lat: g.lat,
                lng: g.lng,
                name: g.names[0] + (g.count > 1 ? ' (' + g.count + ' waypoints)' : ''),
                _kind: 'waypoint',
                // GLOBE-05: only single authoritative altitude is visualized.
                // Multiple distinct or missing => null => baseline 0.012.
                _altitude: (g.altitudes.length === 1) ? g.altitudes[0] : null,
                _count: g.count,
            };
        });
        var heroPoints = pins.map(function (p) { p._kind = 'hero'; return p; });
        var allPoints = heroPoints.concat(cityPoints);

        globe.pointsData(allPoints)
            .pointLat('lat').pointLng('lng')
                        .pointColor(function (d) {
                                return d._kind === 'waypoint' ? '#991b1b' : (colorMap[d.category] || '#2563eb');
            })
            .pointAltitude(function (d) {
                if (d._kind === 'waypoint' && typeof d._altitude === 'number' && isFinite(d._altitude)) {
                    return Math.max(0.012, d._altitude / 30000);
                }
                return 0.012;
            })
            .pointRadius(function (d) { return d._kind === 'waypoint' ? 0.25 : 0.42; })
            .pointLabel(function (d) {
                var alt = (d._kind === 'waypoint' && d._altitude) ? ' (' + d._altitude + 'm)' : '';
                return '<div style="background:#fff;padding:10px 14px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.15);font-family:Inter,sans-serif;font-size:12px;font-weight:600;color:#111827;">' + d.name + alt + '</div>';
            })
            .onPointClick(function () {
                var s = document.getElementById('mapSection');
                if (s) s.scrollIntoView({ behavior: 'smooth' });
            });
    });

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
    __nepalMapInstance = map;

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CartoDB', subdomains: 'abcd', maxZoom: 19
    }).addTo(map);

    // ─────────── DISTRICT BOUNDARY LAYER (GLOBE-02) ───────────
    // Source: Open Knowledge Nepal localboundaries (CC BY 4.0)
    // TopoJSON object: "districts" - 77 polygons
    map.createPane('districtsPane');
    map.getPane('districtsPane').style.zIndex = 350;

    fetch('/map/nepal-districts.topojson')
        .then(function (res) { return res.json(); })
        .then(function (topo) {
            if (typeof topojson === 'undefined') {
                console.warn('topojson-client not loaded; skipping district layer');
                return;
            }
            const geo = topojson.feature(topo, topo.objects.districts);

            L.geoJSON(geo, {
                pane: 'districtsPane',
                style: function () { return districtBaseStyle; },
                onEachFeature: function (feature, layer) {
                    const props = feature.properties || {};
                    const displayName = titleCaseDistrict(props.DISTRICT || '');
                    const provinceName = props.PR_NAME || '';

                    const tooltipHtml =
                        '<div style="font-weight:600;font-size:12px;color:#111827;">' + displayName + '</div>' +
                        '<div style="font-size:10px;color:#6b7280;margin-top:1px;">' + provinceName + '</div>';

                    layer.bindTooltip(tooltipHtml, {
                        sticky: true,
                        direction: 'top',
                        className: 'district-tooltip',
                    });

                                        layer.on('mouseover', function () {
                        if (__selectedDistrictLayer !== layer) {
                            layer.setStyle(districtHoverStyle);
                        }
                    });
                    layer.on('mouseout', function () {
                        if (__selectedDistrictLayer !== layer) {
                            layer.setStyle(districtBaseStyle);
                        } else {
                            layer.setStyle(districtSelectedStyle);
                        }
                    });
                    layer.on('click', function () {
                        openDistrictPanel(feature.properties || {}, layer, map);
                    });
                },
            }).addTo(map);
        })
                .catch(function (err) {
            console.warn('District boundary layer failed to load:', err);
        });

        // ─────────── WAYPOINT MARKERS (GLOBE-03, H1 + D4 dedup) ───────────
    // Source: /api/map/init (shared promise — no additional fetch)
    // Visual: green circleMarker, distinct from hero pins
    // Behavior: always visible (not affected by .map-filter)
    // D4: 25 city waypoints grouped by rounded coordinate → ~7 unique markers
    loadMapData().then(function (mapData) {
        if (!mapData || !Array.isArray(mapData.waypoints)) return;
        var cityGroups = groupCityWaypoints(mapData.waypoints);
        if (cityGroups.length === 0) return;

        var wpLayer = L.layerGroup();
        cityGroups.forEach(function (g) {
                        var marker = L.circleMarker([g.lat, g.lng], {
                radius: 7,
                                fillColor: '#991b1b',
                color: '#ffffff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.9,
            });

            var title = g.names[0] + (g.count > 1 ? ' (' + g.count + ' waypoints)' : '');

            var altLine = (g.altitudes.length > 0)
                ? '<div style="font-size:11px;color:#6b7280;margin-top:2px;">Alt: ' + g.altitudes[0] + ' m</div>'
                : '';

            var nameList = (g.count > 1 && g.names.length > 1)
                ? '<div style="font-size:10px;color:#6b7280;margin-top:3px;border-top:1px solid #e5e7eb;padding-top:3px;">'
                    + 'At this location: ' + g.names.join(', ') + '</div>'
                : '';

            var popup =
                '<div style="font-weight:600;font-size:13px;color:#111827;">' + title + '</div>' +
                '<div style="font-size:11px;color:#6b7280;margin-top:2px;">Type: ' + g.typeList.join(', ') + '</div>' +
                altLine +
                nameList;

                        marker.bindPopup(popup);
            marker.addTo(wpLayer);
            g._marker = marker; // GLOBE-03 v2: for chip navigation
        });
        wpLayer.addTo(map);
        buildCityChips(cityGroups, map); // GLOBE-03 v2
    });

    // GLOBE-03 v2: Hint badge auto-hide at zoom >= 9
    map.on('zoomend', function () {
        var hint = document.getElementById('waypointHint');
        if (!hint) return;
        if (map.getZoom() >= 9) {
            hint.style.opacity = '0';
            hint.style.pointerEvents = 'none';
        } else {
            hint.style.opacity = '1';
            hint.style.pointerEvents = 'auto';
        }
    });


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
// ═══════════════ GLOBE-06: ROUTE SELECTOR ═══════════════

function initRouteSelector() {
    var sel = document.getElementById('routeSelector');
    if (!sel) return;
    sel.addEventListener('change', function () {
        handleRouteSelect(sel.value);
    });
    loadMapData().then(function (mapData) {
        if (mapData && Array.isArray(mapData.routes)) {
            populateRouteSelector(mapData.routes);
        }
    });
}

function populateRouteSelector(routes) {
    var sel = document.getElementById('routeSelector');
    if (!sel) return;
    var sorted = routes.slice().sort(function (a, b) {
        return String(a.name).localeCompare(String(b.name));
    });
    sorted.forEach(function (r) {
        var opt = document.createElement('option');
        opt.value = r.slug;
        opt.textContent = r.name + ' · ' + (r.type || '') + ' · ' + (r.difficulty || '');
        sel.appendChild(opt);
    });
}

function handleRouteSelect(slug) {
    __selectedRouteSlug = slug || null;
    var token = ++__routeRequestToken;

    clearLeafletRoute();
    clearGlobeRoute();
    hideRouteMeta();
    hideRouteError();

    if (!slug) return;

    fetch('/api/map/route/' + encodeURIComponent(slug))
        .then(function (r) {
            return r.json().then(function (j) {
                return { status: r.status, body: j };
            });
        })
        .then(function (res) {
            if (token !== __routeRequestToken) return;
            if (!res.body || !res.body.success) {
                showRouteError('Route not found');
                return;
            }
            var data = res.body.data || {};
            if (data.route) showRouteMeta(data.route);
            if (Array.isArray(data.geometry) && data.geometry.length >= 2) {
                renderLeafletRoute(data.geometry);
                renderGlobeRoute(data.geometry);
            }
        })
        .catch(function (err) {
            if (token !== __routeRequestToken) return;
            console.warn('Route fetch failed:', err);
            showRouteError('Could not load route');
        });
}

function renderLeafletRoute(geometry) {
    if (!__nepalMapInstance) return;
    var latlngs = geometry.map(function (p) { return [p.lat, p.lng]; });
    __leafletRouteLayer = L.polyline(latlngs, {
        color: '#dc2626',
        weight: 4,
        opacity: 0.9,
    }).addTo(__nepalMapInstance);
    try {
        __nepalMapInstance.fitBounds(__leafletRouteLayer.getBounds(), {
            padding: [40, 40],
            maxZoom: 12,
        });
    } catch (e) { /* ignore */ }
}

function clearLeafletRoute() {
    if (__leafletRouteLayer && __nepalMapInstance) {
        __nepalMapInstance.removeLayer(__leafletRouteLayer);
    }
    __leafletRouteLayer = null;
}

function renderGlobeRoute(geometry) {
    if (!__globeInstance) return;
    var coords = geometry.map(function (p) { return [p.lat, p.lng, 0.015]; });
    if (!__globeRouteAccessorsSet) {
        __globeInstance
            .pathPoints('coords')
            .pathPointLat(function (p) { return p[0]; })
            .pathPointLng(function (p) { return p[1]; })
            .pathPointAlt(function (p) { return p[2]; })
            .pathColor(function () { return '#dc2626'; })
            .pathStroke(2)
            .pathTransitionDuration(600);
        __globeRouteAccessorsSet = true;
    }
    __globeInstance.pathsData([{ coords: coords }]);
}

function clearGlobeRoute() {
    if (__globeInstance) {
        __globeInstance.pathsData([]);
    }
}

function showRouteMeta(route) {
    var el = document.getElementById('routeMeta');
    if (!el) return;
    var esc = (typeof escapeHtmlSafe === 'function') ? escapeHtmlSafe : function (s) { return String(s); };
    var html = '<div class="font-semibold text-gray-800 mb-1">' + esc(route.name || '') + '</div>' +
        '<div class="grid grid-cols-2 gap-1 text-gray-600">' +
        '<span>Type: <strong>' + esc(route.type || '—') + '</strong></span>' +
        '<span>Difficulty: <strong>' + esc(route.difficulty || '—') + '</strong></span>' +
        '<span>Duration: <strong>' + (route.duration_days ? route.duration_days + ' days' : '—') + '</strong></span>' +
        '<span>Max altitude: <strong>' + (route.max_altitude ? route.max_altitude + ' m' : '—') + '</strong></span>' +
        '</div>';
    el.innerHTML = html;
    el.classList.remove('hidden');
}

function hideRouteMeta() {
    var el = document.getElementById('routeMeta');
    if (el) {
        el.classList.add('hidden');
        el.innerHTML = '';
    }
}

function showRouteError(msg) {
    var el = document.getElementById('routeError');
    if (!el) return;
    el.textContent = msg;
    el.classList.remove('hidden');
}

function hideRouteError() {
    var el = document.getElementById('routeError');
    if (el) {
        el.classList.add('hidden');
        el.textContent = '';
    }
}

// ═══════════════════════════════════════════════════════════
// INIT + HANDLERS
// ═══════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {
    // GLOBE-04: Wire district panel close handlers
    var _dpc = document.getElementById('districtPanelClose');
    if (_dpc) _dpc.addEventListener('click', closeDistrictPanel);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDistrictPanel();
    });

    initGlobe();
    initMap();
    initRouteSelector();  // GLOBE-06

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