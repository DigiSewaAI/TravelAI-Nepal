@extends('layouts.public')

@section('title', __('messages.travel_safety_nepal'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    
    <!-- Page Header with Last Updated -->
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 flex items-center gap-3">
                <span class="text-4xl">🇳🇵</span>
                {{ __('messages.travel_safety_nepal') }}
            </h1>
            <p class="text-gray-600 mt-2 max-w-2xl">
                {{ __('messages.safety_subtitle') ?? 'Real-time safety updates, AI-driven risk assessments, and live incident tracking for travelers across Nepal.' }}
            </p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500 bg-gray-50 px-4 py-2 rounded-full border border-gray-200">
            <i class="fas fa-sync-alt text-blue-500 animate-spin-slow"></i>
            <span>Last updated: {{ now()->format('M d, Y h:i A') }}</span>
        </div>
    </div>
    
    <!-- Advanced Summary Cards -->
    @php
        $computedStats = [
            'normal'    => $incidents->where('severity', 'normal')->count(),
            'caution'   => $incidents->where('severity', 'moderate')->count(),
            'high_risk' => $incidents->whereIn('severity', ['high', 'high_risk'])->count(),
            'avoid'     => $incidents->whereIn('severity', ['critical', 'avoid'])->count(),
        ];
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @php
            $statsConfig = [
                ['key' => 'normal', 'label' => __('messages.status_normal'), 'color' => 'emerald', 'icon' => 'fa-check-circle'],
                ['key' => 'caution', 'label' => __('messages.status_caution'), 'color' => 'amber', 'icon' => 'fa-exclamation-circle'],
                ['key' => 'high_risk', 'label' => __('messages.status_high_risk'), 'color' => 'orange', 'icon' => 'fa-radiation'],
                ['key' => 'avoid', 'label' => __('messages.status_avoid'), 'color' => 'red', 'icon' => 'fa-ban'],
            ];
        @endphp

        @foreach($statsConfig as $stat)
            <div class="glass-card rounded-2xl p-5 border-l-4 border-{{ $stat['color'] }}-500 bg-{{ $stat['color'] }}-50/40 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-{{ $stat['color'] }}-700 uppercase tracking-wider">{{ $stat['label'] }}</p>
                        <h2 class="text-3xl font-extrabold text-{{ $stat['color'] }}-900 mt-1">
                            {{ $computedStats[$stat['key']] ?? 0 }}
                        </h2>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-{{ $stat['color'] }}-100 flex items-center justify-center text-{{ $stat['color'] }}-600 shadow-sm">
                        <i class="fas {{ $stat['icon'] }} text-xl"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- ========== NEW: Search & Weather Section ========== -->
    <div class="mb-8 space-y-4">
        <!-- Search Box -->
        <div class="relative max-w-2xl">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                <i class="fas fa-search text-blue-500 mr-1"></i>
                {{ __('messages.check_destination_weather_safety') }}
            </label>
            <div class="relative">
                <input type="text" id="safetySearch"
                       placeholder="{{ __('messages.search_placeholder') }}"
                       class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-3 focus:ring-2 focus:ring-blue-500 shadow-sm transition">
                <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
                <div id="searchResults" class="absolute left-0 right-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-200 hidden z-50 max-h-96 overflow-y-auto"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1.5">Search from 138+ destinations across Nepal</p>
        </div>

        <!-- Weather Snapshot Strip (compact) -->
        @if(isset($weatherStrip) && count(array_filter($weatherStrip)) > 0)
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-xl px-4 py-2.5 flex flex-wrap items-center justify-between text-sm">
                <div class="flex items-center gap-2 text-gray-700 font-medium">
                    <i class="fas fa-cloud-sun text-yellow-500"></i>
                    <span>{{ __('messages.weather_across_nepal') }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-gray-700">
                    @foreach($weatherStrip as $city => $data)
                        @if($data)
                            <span class="inline-flex items-center gap-1.5">
                                <span class="font-medium">{{ $city }}</span>
                                @if($data && isset($data['temp']) && $data['temp'] > 0)
    <span class="text-blue-600 font-bold">{{ $data['temp'] }}°C</span>
@else
    <span class="text-gray-400 text-xs">—</span>
@endif
                                @if($data['icon'])
                                    <img src="https://openweathermap.org/img/wn/{{ $data['icon'] }}.png" class="w-5 h-5" alt="weather icon">
                                @endif
                            </span>
                        @endif
                    @endforeach
                    <span class="text-xs text-gray-400">· {{ now()->diffForHumans() }}</span>
                </div>
            </div>
        @endif
    </div>
    <!-- ========== END NEW SECTION ========== -->

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Map Section (Takes 2/3 width) -->
        <div class="lg:col-span-2">
            <div class="glass-card rounded-2xl p-1 shadow-lg border border-gray-200 overflow-hidden relative">
                <div class="bg-white rounded-xl p-4 md:p-6 relative">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-map-marked-alt text-blue-600"></i> {{ __('messages.safety_map') }}
                        </h2>
                        <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Live Interactive Map</span>
                    </div>
                    
                    <!-- Map Container -->
                    <div id="safetyMap" class="w-full h-[500px] rounded-xl z-0 border border-gray-200 relative">
                        <!-- Loading State -->
                        <div id="mapLoader" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-[1000] flex items-center justify-center rounded-xl">
                            <div class="flex flex-col items-center gap-3">
                                <i class="fas fa-circle-notch fa-spin text-3xl text-blue-600"></i>
                                <span class="text-sm font-medium text-gray-600">Loading map data...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Map Legend -->
                    <div class="absolute bottom-8 right-8 z-[500] bg-white/95 backdrop-blur-md p-3 rounded-xl shadow-lg border border-gray-200 text-xs hidden md:block">
                        <p class="font-bold text-gray-700 mb-2">Map Legend</p>
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-500"></span> Critical / Avoid</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-orange-500"></span> High Risk</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-400"></span> Caution</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Normal</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar: Affected Areas -->
        <div class="lg:col-span-1">
            <div class="glass-card rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-24">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-layer-group text-red-500"></i> {{ __('messages.affected_areas') }}
                </h3>

                <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    @php $hasAreas = false; @endphp
                    
                    @foreach(($affectedWaypoints ?? []) as $wp)
                        @php $hasAreas = true; @endphp
                        @php 
                            $status = strtolower($wp->safety_status ?? 'normal');
                            $badgeColor = match($status) {
                                'caution' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'high_risk', 'high' => 'bg-orange-100 text-orange-800 border-orange-200',
                                'avoid', 'critical' => 'bg-red-100 text-red-800 border-red-200',
                                default => 'bg-emerald-100 text-emerald-800 border-emerald-200'
                            };
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100 hover:bg-white hover:shadow-md transition-all">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-map-pin text-gray-400"></i>
                                <span class="font-medium text-gray-800 text-sm">{{ $wp->name ?? 'Unknown Location' }}</span>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $badgeColor }}">
                                {{ str_replace('_', ' ', $status) }}
                            </span>
                        </div>
                    @endforeach

                    @foreach(($affectedTreks ?? []) as $trek)
                        @php $hasAreas = true; @endphp
                        @php 
                            $status = strtolower($trek->safety_status ?? 'normal');
                            $badgeColor = match($status) {
                                'caution' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'high_risk', 'high' => 'bg-orange-100 text-orange-800 border-orange-200',
                                'avoid', 'critical' => 'bg-red-100 text-red-800 border-red-200',
                                default => 'bg-emerald-100 text-emerald-800 border-emerald-200'
                            };
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100 hover:bg-white hover:shadow-md transition-all">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-mountain text-gray-400"></i>
                                <span class="font-medium text-gray-800 text-sm">{{ $trek->name ?? 'Unknown Trek' }}</span>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $badgeColor }}">
                                {{ str_replace('_', ' ', $status) }}
                            </span>
                        </div>
                    @endforeach

                    @if(!$hasAreas)
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-shield-alt text-3xl text-emerald-400 mb-2"></i>
                            <p class="text-sm">No areas currently under safety alerts.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Active Incidents Section -->
    <div class="mt-10">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-2 h-8 bg-red-500 rounded-full animate-pulse"></div>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.active_incidents') }}</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($incidents as $incident)
                @php
                    $severityColor = match($incident->severity) {
                        'critical' => 'border-red-500 bg-red-50/50',
                        'high' => 'border-orange-500 bg-orange-50/50',
                        'moderate' => 'border-amber-500 bg-amber-50/50',
                        default => 'border-emerald-500 bg-emerald-50/50'
                    };
                    $icon = match($incident->severity) {
                        'critical' => '🔴',
                        'high' => '🟠',
                        'moderate' => '🟡',
                        default => '🟢'
                    };
                @endphp
                <div class="glass-card rounded-2xl p-5 border-l-4 {{ $severityColor }} hover:shadow-lg transition-all duration-300 hover:-translate-y-1 flex flex-col h-full">
                    <div class="flex items-start justify-between mb-3">
                        <span class="text-2xl" title="{{ $incident->severity }}">{{ $icon }}</span>
                        <span class="text-xs font-semibold text-gray-500 bg-white px-2 py-1 rounded-md border border-gray-200">
                            {{ $incident->reported_at?->diffForHumans() ?? 'Recently' }}
                        </span>
                    </div>
                    
                    <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 leading-tight">
                        <a href="{{ route('safety.incident', $incident->id) }}" class="hover:text-blue-600 transition-colors">
                            {{ $incident->title }}
                        </a>
                    </h3>
                    
                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-3">
                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                        <span class="truncate">{{ $incident->location_name ?? 'Unknown Location' }}</span>
                    </div>

                    @if($incident->description)
                        <p class="text-sm text-gray-600 mb-4 line-clamp-3 flex-grow">
                            {{ Str::limit($incident->description, 120) }}
                        </p>
                    @endif

                    <a href="{{ route('safety.incident', $incident->id) }}" class="mt-auto inline-flex items-center justify-center gap-2 w-full py-2.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition-all">
                        {{ __('messages.view_details') }} <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            @empty
                <div class="col-span-full glass-card rounded-2xl p-12 text-center border border-dashed border-gray-300">
                    <i class="fas fa-check-circle text-5xl text-emerald-400 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">All Clear!</h3>
                    <p class="text-gray-500">{{ __('messages.no_active_incidents_reported') ?? 'No active safety incidents reported at this time.' }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ---------- Map ----------
        fetch('{{ route("api.safety.markers") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('mapLoader').style.display = 'none';
                const map = L.map('safetyMap').setView([28.3949, 84.1240], 7);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    className: 'rounded-xl'
                }).addTo(map);

                data.forEach(incident => {
                    const marker = L.circleMarker([incident.latitude, incident.longitude], {
                        radius: 10,
                        color: '#ffffff',
                        fillColor: incident.color,
                        fillOpacity: 0.9,
                        weight: 3
                    }).addTo(map);

                    const popupContent = `
                        <div class="p-1 min-w-[220px]">
                            <strong class="text-gray-900 text-base block mb-1">${incident.title}</strong>
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-bold text-white mb-2" style="background-color: ${incident.color}">
                                ${incident.severity.toUpperCase()}
                            </span>
                            <p class="text-sm text-gray-600 mb-2">${incident.location || ''}</p>
                            <a href="${incident.url}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800">
                                View Details &rarr;
                            </a>
                        </div>
                    `;
                    marker.bindPopup(popupContent);

                    if (incident.affected_radius) {
                        L.circle([incident.latitude, incident.longitude], {
                            radius: incident.affected_radius,
                            color: incident.color,
                            fillColor: incident.color,
                            fillOpacity: 0.1,
                            weight: 1,
                            opacity: 0.4
                        }).addTo(map);
                    }
                });
            })
            .catch(error => {
                console.error('Error loading safety markers:', error);
                document.getElementById('mapLoader').innerHTML = '<span class="text-red-500 text-sm">Failed to load map data</span>';
            });

        // ---------- Search: Weather & Safety ----------
        const searchInput = document.getElementById('safetySearch');
        const resultsContainer = document.getElementById('searchResults');

        // Delay search for better UX
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            if (query.length < 2) {
                resultsContainer.classList.add('hidden');
                return;
            }
            debounceTimer = setTimeout(() => performSearch(query), 300);
        });

        function performSearch(query) {
            fetch(`/safety/search?q=${encodeURIComponent(query)}`)
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    if (!data.found) {
                        resultsContainer.innerHTML = `<div class="p-4 text-gray-500 text-sm">No destinations found</div>`;
                        resultsContainer.classList.remove('hidden');
                        return;
                    }

                    // Data structure: data.results is an array of results
                    if (data.results && data.results.length > 0) {
                        let html = '';
                        data.results.forEach(item => {
                            const statusBadge = item.safety_status === 'avoid' ? 'bg-red-100 text-red-700' : 
                                                (item.safety_status === 'high_risk' ? 'bg-orange-100 text-orange-700' : 'bg-emerald-100 text-emerald-700');
                            const statusLabel = item.safety_status ? item.safety_status.toUpperCase() : 'NORMAL';
                            const incidentInfo = item.incident ? `<div class="text-xs text-red-600 mt-1">⚠️ ${item.incident.title}</div>` : '';

                            html += `
                                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition" onclick="window.location.href='/travel-safety/destination/${item.slug}'">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-bold text-gray-800">${item.name}</h4>
                                            ${item.weather ? `
                                                <div class="flex items-center gap-3 text-sm mt-1">
                                                    <span class="text-gray-600"><i class="fas fa-thermometer-half"></i> ${item.weather.temp}°C</span>
                                                    <span class="text-gray-600 capitalize"><i class="fas fa-cloud"></i> ${item.weather.condition}</span>
                                                    <span class="text-gray-600"><i class="fas fa-tint"></i> ${item.weather.humidity}%</span>
                                                </div>
                                            ` : `<div class="text-xs text-gray-400 mt-1">Weather data unavailable</div>`}
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="text-xs font-bold px-2 py-0.5 rounded-full ${statusBadge}">${statusLabel}</span>
                                                ${incidentInfo}
                                            </div>
                                        </div>
                                        <span class="text-blue-600 text-sm font-medium">View →</span>
                                    </div>
                                </div>
                            `;
                        });
                        resultsContainer.innerHTML = html;
                        resultsContainer.classList.remove('hidden');
                    } else {
                        resultsContainer.innerHTML = `<div class="p-4 text-gray-500 text-sm">No results found</div>`;
                        resultsContainer.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    resultsContainer.innerHTML = `<div class="p-4 text-red-500 text-sm">Error searching. Please try again.</div>`;
                    resultsContainer.classList.remove('hidden');
                });
        }

        // Close search results on click outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#safetySearch') && !e.target.closest('#searchResults')) {
                resultsContainer.classList.add('hidden');
            }
        });
    });
</script>
@endpush
@endsection