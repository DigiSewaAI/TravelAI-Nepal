@extends('layouts.public')

@section('title', $service->name . ' | TravelAI Nepal')
@section('meta_description', Str::limit(strip_tags($service->description), 155))

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "TouristTrip",
  "name": "{{ addslashes($service->name) }}",
  "description": "{{ Str::limit(strip_tags($service->description), 200) }}",
  "image": "{{ $service->cover_image ? asset('storage/' . $service->cover_image) : asset('images/default-share.jpg') }}",
  "url": "{{ url()->current() }}",
  "provider": {
    "@@type": "LocalBusiness",
    "name": "{{ addslashes($service->provider->name) }}",
    "url": "{{ route('public.providers.show', $service->provider->slug ?? $service->provider->id) }}"
  },
  "offers": {
    "@@type": "Offer",
    "price": "{{ $service->price }}",
    "priceCurrency": "{{ $service->currency ?? 'USD' }}",
    "availability": "https://schema.org/InStock",
    "url": "{{ url()->current() }}"
  }
}
</script>
@endpush
@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-blue-600">{{ __('messages.home') }}</a>
        <span class="mx-2">/</span>
        <a href="{{ route('public.services.index') }}" class="hover:text-blue-600">{{ __('messages.explore') }}</a>
        <span class="mx-2">/</span>
        <span>{{ $service->name }}</span>
    </nav>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Gallery/Images -->
        <div>
            @if($service->cover_image)
                <img src="{{ asset('storage/' . $service->cover_image) }}"
                     alt="{{ $service->name }}"
                     class="w-full rounded-xl shadow-lg object-cover h-96">
            @else
                <div class="w-full h-96 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="TravelAI Nepal"
                         class="w-48 h-48 object-contain opacity-50">
                </div>
            @endif

            @php
                $gallery = is_array($service->gallery) ? $service->gallery : json_decode($service->gallery, true) ?? [];
            @endphp

            @if(count($gallery) > 0)
                <div class="grid grid-cols-4 gap-2 mt-2">
                    @foreach(array_slice($gallery, 0, 4) as $image)
                        <img src="{{ asset('storage/' . $image) }}"
                             alt="{{ __('messages.gallery_image') }}"
                             class="w-full h-20 object-cover rounded-lg cursor-pointer hover:opacity-75">
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Service Details -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $service->name }}</h1>

            <div class="flex items-center gap-2 mt-2 flex-wrap">
                <span class="px-2 py-1 text-sm rounded-full bg-blue-100 text-blue-700">
                    {{ $service->category->name ?? __('messages.na') }}
                </span>

                @if($service->averageRating() > 0)
                    <span class="flex items-center text-sm">
                        @for($i=1; $i<=5; $i++)
                            <span class="text-yellow-400 {{ $i <= floor($service->averageRating()) ? 'fas fa-star' : 'far fa-star' }}"></span>
                        @endfor
                        <span class="ml-1 text-gray-600">{{ number_format($service->averageRating(), 1) }} ({{ $service->ratingsCount() }})</span>
                    </span>
                @endif

                @if($service->provider->verification_status === 'verified')
                    <span class="px-2 py-1 text-sm rounded-full bg-green-100 text-green-700">
                        <i class="fas fa-check-circle"></i> {{ __('messages.verified_provider') }}
                    </span>
                @endif
            </div>

            @php
                $currencyService = app(\App\Services\CurrencyService::class);
                $displayCurrency = $currencyService->getDisplayCurrency();
                $baseCurrency = $service->currency ?? 'USD';
                $displayPrice = $currencyService->convert($service->price, $baseCurrency, $displayCurrency);
                $formattedPrice = $currencyService->format($displayPrice, $displayCurrency);
                $showBaseNote = ($baseCurrency !== $displayCurrency);
            @endphp

            <div class="mt-4">
                <p class="text-3xl font-bold text-blue-600">
                    {{ $formattedPrice }}
                    @if($service->trekDetail)
                        <span class="text-sm font-normal text-gray-500">/ {{ __('messages.pax') }}</span>
                        <span class="text-sm font-normal text-gray-400 ml-2">({{ $service->trekDetail->duration_days ?? '1' }} {{ __('messages.days') }})</span>
                    @elseif($service->hotelDetail)
                        <span class="text-sm font-normal text-gray-500">/ {{ __('messages.night') }}</span>
                    @elseif($service->tourDetail)
                        <span class="text-sm font-normal text-gray-500">/ {{ __('messages.person') }}</span>
                    @else
                        <span class="text-sm font-normal text-gray-500">/ {{ __('messages.person') }}</span>
                    @endif
                </p>
                @if($showBaseNote)
                    <p class="text-xs text-gray-400 mt-1">{{ __('messages.base_price') }}: {{ $currencyService->format($service->price, $baseCurrency) }}</p>
                @endif
            </div>

            <!-- Trek Details -->
            @if($service->trekDetail)
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-semibold text-gray-700">{{ __('messages.trek_details') }}</h3>
                    <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                        <div><span class="text-gray-500">{{ __('messages.duration') }}:</span> {{ $service->trekDetail->duration_days }} {{ __('messages.days') }}</div>
                        <div><span class="text-gray-500">{{ __('messages.difficulty') }}:</span> {{ ucfirst($service->trekDetail->difficulty) }}</div>
                        @if($service->trekDetail->max_altitude)
                            <div><span class="text-gray-500">{{ __('messages.max_altitude') }}:</span> {{ $service->trekDetail->max_altitude }}m</div>
                        @endif
                        @if($service->trekDetail->season)
                            <div><span class="text-gray-500">{{ __('messages.season') }}:</span> {{ $service->trekDetail->season }}</div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Tour Details -->
            @if($service->tourDetail)
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-semibold text-gray-700">{{ __('messages.tour_details') }}</h3>
                    <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                        <div><span class="text-gray-500">{{ __('messages.duration') }}:</span> {{ $service->tourDetail->duration_days }} {{ __('messages.days') }}</div>
                    </div>
                </div>
            @endif

            <!-- Description -->
            @if($service->description)
                <div class="mt-4">
                    <h3 class="font-semibold text-gray-700">{{ __('messages.description') }}</h3>
                    <p class="text-gray-600 mt-2">{{ $service->description }}</p>
                </div>
            @endif

            <!-- Provider Info -->
            <div class="mt-6 p-4 border rounded-lg">
                <h3 class="font-semibold text-gray-700">{{ __('messages.provider') }}</h3>
                <div class="flex items-center gap-3 mt-2">
                    @if($service->provider->logo_url)
                        <img src="{{ asset('storage/' . $service->provider->logo_url) }}"
                             alt="{{ $service->provider->name }} logo"
                             class="w-14 h-14 rounded-full object-cover border-2 border-gray-200">
                    @else
                        <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-building text-blue-600 text-xl"></i>
                        </div>
                    @endif
                    <div>
                        <a href="{{ route('public.providers.show', $service->provider->slug ?? $service->provider->id) }}"
                           class="font-medium text-gray-800 hover:text-blue-600">
                            {{ $service->provider->name }}
                        </a>
                        <p class="text-sm text-gray-500">{{ $service->provider->contact_email ?? '' }}</p>
                    </div>
                </div>
            </div>

            <!-- Booking Button -->
            <div class="mt-6">
                <a href="{{ route('public.services.book', $service->slug) }}"
                   class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition shadow-lg hover:shadow-xl text-center block">
                    <i class="fas fa-calendar-check mr-2"></i> {{ __('messages.book_this_service') }}
                </a>
            </div>
        </div>
    </div>

    {{-- PROVIDER-ITINERARY-06: Public Itinerary Renderer --}}
    @if($service->isItineraryPublished() && $service->itineraryDays->isNotEmpty())
        <div class="mt-12">
            <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Itinerary</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $service->itineraryDays->count() }} days</p>
                </div>
                                <div class="flex gap-2">
                    <button type="button" data-itinerary-action="expand"
                            class="text-sm font-semibold px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                        Expand All
                    </button>
                    <button type="button" data-itinerary-action="collapse"
                            class="text-sm font-semibold px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                        Collapse All
                    </button>
                </div>
            </div>

            {{-- PROVIDER-ITINERARY-08: Mini Leaflet Map --}}
            @php
                $itineraryMapPoints = [];
                foreach ($service->itineraryDays as $d) {
                    if ($d->startWaypoint && $d->startWaypoint->latitude && $d->startWaypoint->longitude) {
                        $itineraryMapPoints[] = [
                            'lat'    => (float) $d->startWaypoint->latitude,
                            'lng'    => (float) $d->startWaypoint->longitude,
                            'name'   => $d->startWaypoint->name,
                            'type'   => 'start',
                            'day'    => $d->day_number,
                        ];
                    }
                    if ($d->overnightWaypoint && $d->overnightWaypoint->latitude && $d->overnightWaypoint->longitude) {
                        $itineraryMapPoints[] = [
                            'lat'    => (float) $d->overnightWaypoint->latitude,
                            'lng'    => (float) $d->overnightWaypoint->longitude,
                            'name'   => $d->overnightWaypoint->name,
                            'type'   => 'overnight',
                            'day'    => $d->day_number,
                        ];
                    }
                    if ($d->endWaypoint && $d->endWaypoint->latitude && $d->endWaypoint->longitude) {
                        $itineraryMapPoints[] = [
                            'lat'    => (float) $d->endWaypoint->latitude,
                            'lng'    => (float) $d->endWaypoint->longitude,
                            'name'   => $d->endWaypoint->name,
                            'type'   => 'end',
                            'day'    => $d->day_number,
                        ];
                    }
                }
            @endphp

            @if(count($itineraryMapPoints) > 0)
                <div id="itineraryMiniMap"
                     class="mt-6 rounded-xl overflow-hidden border border-gray-100 bg-gray-50"
                     style="height: 320px;"></div>
                <div class="flex flex-wrap gap-3 mt-3 text-xs text-gray-500">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background:#2563eb;"></span> Start
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background:#10b981;"></span> Overnight
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background:#dc2626;"></span> End
                    </span>
                </div>
            @endif

            <style>
                .itinerary-day summary { list-style: none; cursor: pointer; }
                .itinerary-day summary::-webkit-details-marker { display: none; }
                .itinerary-day summary .fa-chevron-down { transition: transform 0.2s ease; }
                .itinerary-day[open] summary .fa-chevron-down { transform: rotate(180deg); }
            </style>

            <div class="space-y-3">
                @foreach($service->itineraryDays as $index => $day)
                    <details class="itinerary-day bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" {{ $index === 0 ? 'open' : '' }}>
                        <summary class="px-5 py-4 flex justify-between items-center hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">DAY {{ $day->day_number }}</span>
                                <span class="font-semibold text-gray-900">{{ $day->title }}</span>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </summary>

                        <div class="px-5 pb-5 pt-2 space-y-4 border-t border-gray-100">
                            @if($day->description)
                                <p class="text-sm text-gray-600 leading-relaxed">{{ $day->description }}</p>
                            @endif

                            @if($day->startWaypoint || $day->overnightWaypoint || $day->endWaypoint)
                                <div class="flex flex-wrap gap-2">
                                    @if($day->startWaypoint)
                                        <span class="inline-flex items-center gap-1.5 text-xs bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full">
                                            <i class="fas fa-play-circle text-gray-400"></i>
                                            <span class="text-gray-500">Start:</span> {{ $day->startWaypoint->name }}
                                        </span>
                                    @endif
                                    @if($day->overnightWaypoint)
                                        <span class="inline-flex items-center gap-1.5 text-xs bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">
                                            <i class="fas fa-bed text-emerald-500"></i>
                                            <span class="text-emerald-600">Overnight:</span> {{ $day->overnightWaypoint->name }}
                                        </span>
                                    @endif
                                    @if($day->endWaypoint)
                                        <span class="inline-flex items-center gap-1.5 text-xs bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full">
                                            <i class="fas fa-flag-checkered text-gray-400"></i>
                                            <span class="text-gray-500">End:</span> {{ $day->endWaypoint->name }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            @php
                                $hasMeta = $day->distance_km || $day->estimated_time_hours || $day->elevation_gain_m || $day->elevation_loss_m || $day->altitude_m || $day->accommodation || !empty($day->meals_included);
                            @endphp
                            @if($hasMeta)
                                <div class="flex flex-wrap gap-2 text-xs text-gray-600">
                                    @if($day->distance_km)
                                        <span class="bg-gray-50 border border-gray-200 px-2 py-1 rounded">📏 {{ $day->distance_km }} km</span>
                                    @endif
                                    @if($day->estimated_time_hours)
                                        <span class="bg-gray-50 border border-gray-200 px-2 py-1 rounded">⏱ {{ $day->estimated_time_hours }} hrs</span>
                                    @endif
                                    @if($day->elevation_gain_m)
                                        <span class="bg-gray-50 border border-gray-200 px-2 py-1 rounded">↑ {{ $day->elevation_gain_m }} m</span>
                                    @endif
                                    @if($day->elevation_loss_m)
                                        <span class="bg-gray-50 border border-gray-200 px-2 py-1 rounded">↓ {{ $day->elevation_loss_m }} m</span>
                                    @endif
                                    @if($day->altitude_m)
                                        <span class="bg-gray-50 border border-gray-200 px-2 py-1 rounded">⛰ {{ $day->altitude_m }} m</span>
                                    @endif
                                    @if($day->accommodation)
                                        <span class="bg-gray-50 border border-gray-200 px-2 py-1 rounded">🏨 {{ $day->accommodation }}</span>
                                    @endif
                                    @if(!empty($day->meals_included))
                                        @php
                                            $mealMap = ['B' => '🍳', 'L' => '🍱', 'D' => '🍽'];
                                            $mealText = collect($day->meals_included)->map(fn($m) => $mealMap[$m] ?? $m)->implode(' ');
                                        @endphp
                                        <span class="bg-gray-50 border border-gray-200 px-2 py-1 rounded">{{ $mealText }}</span>
                                    @endif
                                </div>
                            @endif

                            @if($day->items->isNotEmpty())
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Activities</h4>
                                    <ul class="space-y-1.5">
                                        @foreach($day->items as $item)
                                            <li class="flex items-start gap-2 text-sm text-gray-700">
                                                <i class="fas fa-circle text-[6px] text-blue-400 mt-2"></i>
                                                <div>
                                                    <span class="font-medium">{{ $item->title }}</span>
                                                    @if($item->is_optional)
                                                        <span class="text-xs text-gray-400 italic ml-1">(optional)</span>
                                                    @endif
                                                    @if($item->description)
                                                        <p class="text-xs text-gray-500 mt-0.5">{{ $item->description }}</p>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($day->media->isNotEmpty())
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Media</h4>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                        @foreach($day->media as $media)
                                            @if($media->media_type === 'image')
                                                <a href="{{ asset('storage/' . $media->file_path) }}" target="_blank" rel="noopener"
                                                   class="block relative group overflow-hidden rounded-lg bg-gray-100">
                                                    <img src="{{ asset('storage/' . $media->file_path) }}"
                                                         alt="{{ $media->alt_text ?? $day->title }}"
                                                         loading="lazy"
                                                         class="w-full h-24 md:h-32 object-cover group-hover:scale-105 transition-transform duration-300">
                                                </a>
                                            @else
                                                <div class="col-span-2 md:col-span-4">
                                                    <video controls preload="metadata" class="w-full rounded-lg max-h-72 bg-black">
                                                        <source src="{{ asset('storage/' . $media->file_path) }}">
                                                        Video unavailable
                                                    </video>
                                                    @if($media->alt_text)
                                                        <p class="text-xs text-gray-500 mt-1">{{ $media->alt_text }}</p>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('[data-itinerary-action]').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var action = btn.dataset.itineraryAction;
                            document.querySelectorAll('.itinerary-day').forEach(function (el) {
                                if (action === 'expand') el.setAttribute('open', '');
                                else el.removeAttribute('open');
                            });
                        });
                    });
                });
            </script>
        </div>
    @endif

    <!-- Related Services -->
    @if($relatedServices && $relatedServices->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('messages.related_services') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @foreach($relatedServices as $related)
                    @php
                        $relBaseCurrency = $related->currency ?? 'USD';
                        $relDisplayPrice = $currencyService->convert($related->price, $relBaseCurrency, $displayCurrency);
                        $relFormatted = $currencyService->format($relDisplayPrice, $displayCurrency);
                    @endphp
                    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition border border-gray-100 group">
                        <div class="h-40 bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center overflow-hidden">
                            @if($related->cover_image)
                                <img src="{{ asset('storage/' . $related->cover_image) }}"
                                     alt="{{ $related->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <img src="{{ asset('images/logo.png') }}"
                                     alt="TravelAI Nepal"
                                     class="w-20 h-20 object-contain opacity-50 group-hover:scale-105 transition-transform duration-300">
                            @endif
                        </div>
                        <div class="p-3">
                            <h4 class="font-semibold text-gray-800 text-sm truncate">{{ $related->name }}</h4>
                            <p class="text-blue-600 font-bold text-sm">{{ $relFormatted }}</p>
                            <a href="{{ route('public.services.show', $related->slug) }}"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium group-hover:underline">
                                {{ __('messages.view_details') }} →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- PROVIDER-ITINERARY-09A: Public Reviews --}}
    @include('public.services._reviews', ['service' => $service, 'reviews' => $reviews])
</div>
@push('scripts')
@if(isset($itineraryMapPoints) && count($itineraryMapPoints) > 0)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('itineraryMiniMap');
        if (!el || typeof L === 'undefined') return;

        var points = @json($itineraryMapPoints);
        if (!points.length) return;

        var map = L.map(el, {
            zoomControl: true,
            scrollWheelZoom: false,
            attributionControl: true,
        });

        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CartoDB',
            subdomains: 'abcd',
            maxZoom: 19,
        }).addTo(map);

        var colorByType = {
            start:     '#2563eb',
            overnight: '#10b981',
            end:       '#dc2626',
        };

        var bounds = [];
        points.forEach(function (p) {
            var color = colorByType[p.type] || '#6b7280';
            var marker = L.circleMarker([p.lat, p.lng], {
                radius: 8,
                fillColor: color,
                color: '#ffffff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.95,
            }).addTo(map);

            var dayLabel = 'Day ' + p.day + ' · ' + p.type.charAt(0).toUpperCase() + p.type.slice(1);
            var safeName = document.createElement('div');
            safeName.textContent = p.name;
            marker.bindPopup(
                '<div style="font-weight:600;font-size:13px;color:#111827;">' + safeName.innerHTML + '</div>' +
                '<div style="font-size:11px;color:#6b7280;margin-top:2px;">' + dayLabel + '</div>'
            );
            bounds.push([p.lat, p.lng]);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 12 });
        }
    });
</script>
@endif
@endpush
@endsection