@extends('layouts.public')

@section('title', 'My Journey Replay - TravelAI Nepal')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">

    {{-- HERO --}}
    <div class="relative rounded-3xl overflow-hidden mb-8 bg-gradient-to-r from-indigo-900 to-purple-800 text-white shadow-2xl">
        <div class="absolute inset-0 bg-black opacity-30"></div>
        <div class="relative z-10 p-8 md:p-12">
            <h1 class="text-4xl md:text-6xl font-bold mb-2">🎬 My Journey Replay</h1>
            <p class="text-xl md:text-2xl text-blue-200">
                {{ $replayData['has_data'] ? ($replayData['story'] ? '"A Journey to Remember"' : 'Your travels, remembered beautifully.') : 'Your journey is waiting to be written.' }}
            </p>
            @if($replayData['has_data'])
                <div class="mt-4 flex flex-wrap gap-4 text-sm">
                    <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full">{{ $replayData['stats']['total_bookings'] }} Bookings</span>
                    <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full">{{ $replayData['stats']['total_checkins'] }} Check-ins</span>
                    <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full">{{ $replayData['stats']['unique_destinations'] }} Destinations</span>
                    @if($replayData['stats']['highest_altitude'] > 0)
                        <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full">{{ $replayData['stats']['highest_altitude'] }}m Highest</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if(!$replayData['has_data'])
        {{-- EMPTY STATE --}}
        <div class="text-center py-16">
            <div class="text-6xl mb-4">🗺️</div>
            <h2 class="text-2xl font-bold text-gray-700">No journey yet</h2>
            <p class="text-gray-500 mt-2">Book your first TravelAI Nepal experience and your memories will appear here.</p>
            <a href="{{ route('public.services.index') }}" class="inline-block mt-4 bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition">Explore Experiences →</a>
        </div>
    @else
        {{-- STATS BAR --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $replayData['stats']['total_bookings'] }}</div>
                <div class="text-xs text-gray-500">Total Bookings</div>
            </div>
            <div class="bg-white rounded-xl shadow p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $replayData['stats']['total_checkins'] }}</div>
                <div class="text-xs text-gray-500">Check-ins</div>
            </div>
            <div class="bg-white rounded-xl shadow p-4 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $replayData['stats']['unique_destinations'] }}</div>
                <div class="text-xs text-gray-500">Destinations</div>
            </div>
            <div class="bg-white rounded-xl shadow p-4 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $replayData['stats']['highest_altitude'] }}m</div>
                <div class="text-xs text-gray-500">Highest Altitude</div>
            </div>
        </div>

        {{-- AI STORY (if available) --}}
        @if($replayData['story'])
            <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 rounded-2xl p-6 md:p-8 mb-8 border border-blue-100 shadow-inner">
                <div class="flex items-start gap-4">
                    <div class="text-4xl flex-shrink-0">📖</div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-lg font-semibold text-gray-800">Your Journey Story</h3>
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">✨ AI-Generated</span>
                        </div>
                        <p class="text-gray-700 italic leading-relaxed text-base md:text-lg">{{ $replayData['story'] }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-gray-50 rounded-2xl p-6 mb-8 border border-gray-200 text-center">
                <p class="text-gray-400 text-sm">✨ An AI story could not be generated. Your timeline is still available below.</p>
            </div>
        @endif

        {{-- MAP --}}
        @if(count($replayData['map_points']) > 0)
            <div class="bg-white rounded-xl shadow-lg p-4 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-3">🗺️ Your Journey Path</h2>
                <div id="journey-map" style="height: 400px; width: 100%;" class="rounded-lg"></div>
            </div>
        @endif

        {{-- TIMELINE --}}
        <h2 class="text-2xl font-bold text-gray-800 mb-4">📅 Journey Timeline</h2>
        <div class="space-y-6">
            @foreach($replayData['timeline'] as $event)
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-lg transition">
                    <div class="flex flex-col md:flex-row">
                        {{-- Thumbnail --}}
                        <div class="md:w-48 h-48 flex-shrink-0 bg-gray-200 relative">
                            @if($event['cover_image'])
                                <img src="{{ asset('storage/' . $event['cover_image']) }}" alt="{{ $event['service_name'] }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-4xl bg-gray-100">
                                    @if($event['type'] === 'trek') 🥾
                                    @elseif($event['type'] === 'tour') ✈️
                                    @elseif($event['type'] === 'hotel') 🏨
                                    @else 🧳 @endif
                                </div>
                            @endif
                        </div>
                        {{-- Content --}}
                        <div class="flex-1 p-5">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold uppercase tracking-wider text-blue-600">{{ $event['type'] }}</span>
                                        @if($event['status'] === 'completed')
                                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Completed</span>
                                        @elseif($event['status'] === 'confirmed')
                                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Upcoming</span>
                                        @endif
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900">{{ $event['service_name'] }}</h3>
                                    @if($event['provider'])
                                        <p class="text-sm text-gray-500">{{ $event['provider'] }}</p>
                                    @endif
                                </div>
                                <div class="text-right text-sm text-gray-500">
                                    <div>{{ $event['start_date'] ? $event['start_date']->format('M d, Y') : 'N/A' }}</div>
                                    @if($event['duration'])
                                        <div>{{ $event['duration'] }} days</div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-3 text-sm">
                                @if($event['location'])
                                    <span class="bg-gray-100 px-2 py-1 rounded">📍 {{ $event['location'] }}</span>
                                @endif
                                @if($event['type'] === 'trek' && isset($event['difficulty']))
                                    <span class="bg-gray-100 px-2 py-1 rounded">⛰️ {{ $event['difficulty'] }}</span>
                                @endif
                                @if($event['type'] === 'hotel' && isset($event['star_rating']))
                                    <span class="bg-gray-100 px-2 py-1 rounded">⭐ {{ $event['star_rating'] }}</span>
                                @endif
                                <span class="bg-gray-100 px-2 py-1 rounded">📸 {{ $event['checkins']->count() }} check-ins</span>
                                @if($event['rating'])
                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded">★ {{ $event['rating'] }}</span>
                                @endif
                            </div>

                            @if($event['checkins']->isNotEmpty())
                                <div class="mt-3 text-xs text-gray-400">
                                    <span class="font-medium">Checkpoints:</span>
                                    @foreach($event['checkins']->take(3) as $checkin)
                                        <span class="inline-block bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full mr-1">{{ $checkin['checkpoint'] }}</span>
                                    @endforeach
                                    @if($event['checkins']->count() > 3)
                                        <span>+{{ $event['checkins']->count() - 3 }} more</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Back button --}}
        <div class="mt-8 text-center">
            <a href="{{ route('traveler.dashboard') }}" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-xl transition">← Back to Dashboard</a>
        </div>
    @endif
</div>

{{-- Leaflet CSS & JS (CDN) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@if($replayData['has_data'] && count($replayData['map_points']) > 0)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const map = L.map('journey-map').setView([28.3949, 84.1240], 7);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const points = @json($replayData['map_points']);
        const latlngs = points.map(p => [p.lat, p.lng]);

        if (latlngs.length > 1) {
            const polyline = L.polyline(latlngs, { color: '#2563eb', weight: 4, opacity: 0.7 }).addTo(map);
            map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
        } else if (latlngs.length === 1) {
            map.setView(latlngs[0], 12);
        }

        points.forEach(p => {
            const marker = L.marker([p.lat, p.lng]).addTo(map);
            let popupContent = `<strong>${p.name}</strong>`;
            if (p.altitude) popupContent += `<br>Altitude: ${p.altitude}m`;
            if (p.scanned_at) popupContent += `<br>${new Date(p.scanned_at).toLocaleDateString()}`;
            marker.bindPopup(popupContent);
        });
    });
</script>
@endif

@endsection