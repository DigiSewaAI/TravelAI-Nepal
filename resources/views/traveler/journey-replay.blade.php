@extends('layouts.public')

@section('title', 'My Journey Replay - TravelAI Nepal')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">

    {{-- ===============================================
        CINEMATIC HERO
    =============================================== --}}
    <div class="relative rounded-3xl overflow-hidden mb-8 bg-gradient-to-br from-indigo-900 via-purple-800 to-gray-900 text-white shadow-2xl hero-section">
        <div class="absolute inset-0 opacity-20" style="background: radial-gradient(circle at 30% 50%, rgba(255,255,255,0.08) 0%, transparent 70%);"></div>
        <div class="absolute inset-0 bg-black/10"></div>

        <div class="relative z-10 p-8 md:p-12 lg:p-16 text-center">
            <p class="text-sm uppercase tracking-[0.3em] text-purple-300 mb-3 font-light">✨ My Journey Replay</p>

            <h1 class="text-4xl md:text-5xl lg:text-7xl font-bold mb-4 leading-[1.1] tracking-tight">
                @if($replayData['has_data'])
                    {{ $replayData['story'] ? '"A Journey to Remember"' : 'Your Journey. Your Story.' }}
                @else
                    Your journey is waiting to be written.
                @endif
            </h1>

            @if($replayData['has_data'])
                <p class="text-lg md:text-xl lg:text-2xl text-blue-100/80 max-w-2xl mx-auto font-light leading-relaxed">
                    A collection of places, moments and experiences from your journey.
                </p>

                <div class="mt-6 flex flex-wrap justify-center gap-3 md:gap-4 text-sm">
                    <span class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/5">
                        {{ $replayData['stats']['total_bookings'] }} <span class="text-blue-200/70">Experiences</span>
                    </span>
                    <span class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/5">
                        {{ $replayData['stats']['total_checkins'] }} <span class="text-blue-200/70">Moments</span>
                    </span>
                    <span class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/5">
                        {{ $replayData['stats']['unique_places'] }} <span class="text-blue-200/70">Places</span>
                    </span>
                    @if($replayData['stats']['highest_altitude'] > 0)
                        <span class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/5">
                            {{ number_format($replayData['stats']['highest_altitude']) }}m <span class="text-blue-200/70">Highest</span>
                        </span>
                    @endif
                    @if($replayData['stats']['journey_start'] && $replayData['stats']['journey_end'])
                        <span class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/5">
                            {{ $replayData['stats']['journey_start']->format('M d') }} – {{ $replayData['stats']['journey_end']->format('M d, Y') }}
                        </span>
                    @endif
                </div>

                <div class="mt-8">
                    <a href="#journey-timeline"
                       class="inline-block bg-white text-indigo-900 px-8 py-3.5 rounded-full font-semibold hover:bg-gray-100 transition shadow-lg hover:shadow-xl hover:scale-[1.02] transform duration-300 group">
                        ↓ <span class="ml-2">Begin Replay</span>
                    </a>
                </div>

                <div class="mt-6 text-blue-200/30 text-xs tracking-[0.2em] animate-pulse">S C R O L L</div>
            @endif
        </div>
    </div>

    {{-- ===============================================
        EMPTY STATE
    =============================================== --}}
    @if(!$replayData['has_data'])
        <div class="text-center py-20">
            <div class="text-7xl mb-6">🗺️</div>
            <h2 class="text-3xl font-bold text-gray-700">No journey yet</h2>
            <p class="text-gray-400 mt-3 max-w-md mx-auto">Book your first TravelAI Nepal experience and your memories will appear here.</p>
            <a href="{{ route('public.services.index') }}" class="inline-block mt-6 bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700 transition shadow-lg">
                Explore Experiences →
            </a>
        </div>
    @else

        {{-- ===============================================
            JOURNEY HIGHLIGHTS
        =============================================== --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-10" id="stats-container">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 text-center border border-gray-100/80 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="text-3xl md:text-4xl font-bold text-blue-600 stat-number" data-target="{{ $replayData['stats']['total_bookings'] }}">0</div>
                <div class="text-xs md:text-sm text-gray-500 mt-1 font-medium tracking-wide">Experiences</div>
            </div>
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 text-center border border-gray-100/80 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="text-3xl md:text-4xl font-bold text-green-600 stat-number" data-target="{{ $replayData['stats']['total_checkins'] }}">0</div>
                <div class="text-xs md:text-sm text-gray-500 mt-1 font-medium tracking-wide">Moments</div>
            </div>
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 text-center border border-gray-100/80 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="text-3xl md:text-4xl font-bold text-purple-600 stat-number" data-target="{{ $replayData['stats']['unique_places'] }}">0</div>
                <div class="text-xs md:text-sm text-gray-500 mt-1 font-medium tracking-wide">Places</div>
            </div>
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 text-center border border-gray-100/80 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="text-3xl md:text-4xl font-bold text-orange-600 stat-number" data-target="{{ $replayData['stats']['highest_altitude'] }}">0</div>
                <div class="text-xs md:text-sm text-gray-500 mt-1 font-medium tracking-wide">Highest Altitude</div>
            </div>
        </div>

        {{-- ===============================================
            YOUR JOURNEY STORY — FINAL POLISH
        =============================================== --}}
        @if($replayData['story'])
            <div class="relative rounded-3xl p-7 md:p-10 mb-10 overflow-hidden story-section">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50/70 via-indigo-50/50 to-purple-50/70"></div>
                <div class="absolute top-0 right-0 w-48 h-48 bg-purple-200/15 rounded-full blur-3xl -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-40 h-40 bg-blue-200/15 rounded-full blur-3xl -ml-16 -mb-16"></div>

                <div class="relative z-10 max-w-3xl mx-auto text-center">
                    <div class="text-5xl text-purple-300/40 font-serif leading-none mb-1">"</div>
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <span class="text-xs uppercase tracking-[0.2em] text-gray-400 font-medium">Your Journey Story</span>
                    </div>
                    <div class="text-lg md:text-xl lg:text-2xl text-gray-800 leading-relaxed font-light italic max-w-2xl mx-auto">
                        {{ $replayData['story'] }}
                    </div>
                    <div class="mt-6 text-xs text-gray-400 tracking-wide">— TravelAI Nepal</div>
                </div>
            </div>
        @else
            <div class="bg-gray-50/80 rounded-3xl p-8 mb-10 border border-gray-100 text-center">
                <p class="text-gray-400">✨ A story from your journey will appear here.</p>
            </div>
        @endif

        {{-- ===============================================
            MAP — INTEGRATED
        =============================================== --}}
        @if(count($replayData['map_points']) > 0)
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100/80 p-4 md:p-6 mb-10">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-2xl">🗺️</span>
                    <h2 class="text-xl font-semibold text-gray-800">Your Journey Path</h2>
                </div>
                <p class="text-sm text-gray-400 mb-4">Follow the places your journey took you.</p>
                <div id="journey-map" class="rounded-xl overflow-hidden shadow-inner" style="height: 400px; width: 100%;"></div>
                <div class="flex flex-wrap gap-6 mt-3 text-xs text-gray-400/70">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-600 inline-block"></span> Checkpoint
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-6 h-0.5 bg-blue-400 inline-block"></span> Journey path
                    </span>
                </div>
            </div>
        @endif

        {{-- ===============================================
            JOURNEY CHAPTERS
        =============================================== --}}
        <div id="journey-timeline" class="mt-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-10 text-center tracking-tight">
                📖 Your Journey Chapters
            </h2>

            <div class="relative space-y-10">
                @php
                    $chapterLabels = [
                        'tour' => 'Exploring Nepal',
                        'trek' => 'Into the Mountains',
                        'hotel' => 'A Place to Rest',
                        'other' => 'Experience',
                        'transport' => 'On the Road',
                    ];
                    $chapterCounter = 1;
                    $totalChapters = count($replayData['timeline']);
                @endphp

                @foreach($replayData['timeline'] as $index => $event)
                    @php
                        $type = $event['type'];
                        $label = $chapterLabels[$type] ?? 'The Journey Continues';
                        if ($chapterCounter === 1) $label = 'The Journey Begins';
                        if ($chapterCounter === $totalChapters) $label = 'Until Next Time';

                        $icon = match($type) {
                            'trek' => '🥾',
                            'tour' => '✈️',
                            'hotel' => '🏨',
                            default => '🧳'
                        };
                        $displayType = match($type) {
                            'trek' => 'TREK',
                            'tour' => 'TOUR',
                            'hotel' => 'HOTEL',
                            default => 'EXPERIENCE'
                        };

                        $uniqueCheckpoints = $event['checkins']->pluck('checkpoint')->unique()->values();
                        $visibleCheckpoints = $uniqueCheckpoints->take(3);
                        $extraCheckpoints = max(0, $uniqueCheckpoints->count() - 3);
                    @endphp

                    <div class="flex flex-col md:flex-row gap-4 items-start animate-fade-up chapter-card">
                        <div class="md:w-24 flex-shrink-0 text-right">
                            <span class="text-xs font-bold text-blue-600/60 uppercase tracking-[0.15em]">Chapter {{ str_pad($chapterCounter, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>

                        <div class="flex-1 bg-white rounded-3xl shadow-sm border border-gray-100/80 overflow-hidden hover:shadow-lg transition-all duration-300 group">
                            <div class="flex flex-col md:flex-row">
                                {{-- ✅ FIXED IMAGE CONTAINER with consistent aspect ratio --}}
                                <div class="md:w-44 h-44 flex-shrink-0 bg-gray-100 relative overflow-hidden">
                                    @if($event['cover_image'])
                                        <img src="{{ asset('storage/' . $event['cover_image']) }}"
                                             alt="{{ $event['service_name'] }}"
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                             style="aspect-ratio: 1/1; object-fit: cover;"
                                             loading="lazy">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-5xl bg-gradient-to-br from-gray-50 to-gray-100 text-gray-300">
                                            {{ $icon }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1 p-5 md:p-6">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                                <span class="text-xs font-semibold uppercase tracking-wider text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-full">{{ $displayType }}</span>
                                                @if($event['status'] === 'completed')
                                                    <span class="text-xs text-green-700 bg-green-50 px-2.5 py-0.5 rounded-full">Completed</span>
                                                @endif
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors duration-300">
                                                {{ $event['service_name'] }}
                                            </h3>
                                            @if($event['provider'])
                                                <p class="text-sm text-gray-500">{{ $event['provider'] }}</p>
                                            @endif
                                        </div>

                                        <div class="text-right text-sm text-gray-500 flex-shrink-0">
                                            <div>{{ $event['start_date'] ? $event['start_date']->format('M d, Y') : 'N/A' }}</div>
                                            @if($event['duration'])
                                                <div class="text-xs text-gray-400">{{ $event['duration'] }} days</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                        @if($event['location'])
                                            <span class="bg-blue-50 text-blue-700 px-2.5 py-0.5 rounded-full">📍 {{ $event['location'] }}</span>
                                        @endif
                                        @if($event['type'] === 'trek' && isset($event['difficulty']))
                                            <span class="bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full">⛰️ {{ ucfirst($event['difficulty']) }}</span>
                                        @endif
                                        @if($event['type'] === 'hotel' && isset($event['star_rating']))
                                            <span class="bg-yellow-50 text-yellow-700 px-2.5 py-0.5 rounded-full">⭐ {{ $event['star_rating'] }}</span>
                                        @endif
                                        <span class="bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full">📸 {{ $event['checkins']->count() }} check-ins</span>
                                        @if($event['rating'])
                                            <span class="bg-yellow-50 text-yellow-700 px-2.5 py-0.5 rounded-full">★ {{ number_format($event['rating'], 1) }}</span>
                                        @endif
                                    </div>

                                    @if($uniqueCheckpoints->isNotEmpty())
                                        <div class="mt-2 text-xs text-gray-400">
                                            <span class="font-medium text-gray-500">Checkpoints:</span>
                                            @foreach($visibleCheckpoints as $name)
                                                <span class="inline-block bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full mr-1">{{ $name }}</span>
                                            @endforeach
                                            @if($extraCheckpoints > 0)
                                                <span class="text-gray-400">+{{ $extraCheckpoints }} more</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @php $chapterCounter++; @endphp
                @endforeach
            </div>
        </div>

        {{-- ===============================================
            CINEMATIC ENDING
        =============================================== --}}
        <div class="mt-16 text-center border-t border-gray-200/60 pt-12 ending-section">
            <div class="max-w-2xl mx-auto">
                <p class="text-base text-gray-400/80 italic font-light">And just like that...</p>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2 tracking-tight leading-[1.1]">
                    your journey became a memory.
                </h2>

                <div class="flex flex-wrap justify-center gap-4 mt-6">
                    <span class="text-sm text-gray-500 bg-gray-50/80 px-3 py-1.5 rounded-full">
                        {{ $replayData['stats']['total_bookings'] }} <span class="text-gray-400">experiences</span>
                    </span>
                    <span class="text-sm text-gray-500 bg-gray-50/80 px-3 py-1.5 rounded-full">
                        {{ $replayData['stats']['total_checkins'] }} <span class="text-gray-400">moments</span>
                    </span>
                    @if($replayData['stats']['highest_altitude'] > 0)
                        <span class="text-sm text-gray-500 bg-gray-50/80 px-3 py-1.5 rounded-full">
                            {{ number_format($replayData['stats']['highest_altitude']) }}m <span class="text-gray-400">reached</span>
                        </span>
                    @endif
                </div>

                <p class="text-gray-400 mt-6 text-base font-light tracking-wide">Until the next adventure. ❤️</p>
            </div>
        </div>
<div class="text-center mt-8">
    <a href="{{ route('traveler.cinematic-replay') }}"
       class="inline-block bg-gradient-to-r from-pink-500 to-purple-600 text-white px-8 py-3 rounded-full font-semibold shadow-lg hover:shadow-xl transition hover:scale-105">
        🎬 Watch Cinematic Replay
    </a>
</div>
        {{-- Back to Dashboard --}}
        <div class="mt-10 text-center">
            <a href="{{ route('traveler.dashboard') }}"
               class="inline-block text-sm text-gray-400 hover:text-gray-600 transition-colors duration-300">
                ← Back to Dashboard
            </a>
        </div>

    @endif
</div>

{{-- ===============================================
    LEAFTLET (Map)
=============================================== --}}
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

        document.querySelectorAll('.stat-number').forEach(el => {
            const target = parseInt(el.dataset.target, 10);
            if (isNaN(target)) return;
            let current = 0;
            const duration = 800;
            const steps = Math.min(60, Math.max(10, Math.floor(target / 5)));
            const step = Math.max(1, Math.floor(target / steps));
            const interval = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(interval);
                }
                el.textContent = current.toLocaleString();
            }, Math.floor(duration / steps));
        });
    });
</script>
@endif

<style>
    .hero-section .animate-pulse {
        animation: pulse 3s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.7; }
    }
    .animate-fade-up {
        animation: fadeUp 0.6s ease-out forwards;
        opacity: 0;
    }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .chapter-card {
        transition: all 0.3s ease;
    }
    .stat-number {
        font-feature-settings: "tnum";
    }
    .ending-section {
        opacity: 0;
        animation: fadeUp 0.8s ease-out 0.3s forwards;
    }
    @media (max-width: 640px) {
        .hero-section .text-4xl { font-size: 2.25rem; }
        #journey-map { height: 300px !important; }
        .chapter-card .md\:w-44 { height: 140px; }
        .story-section .text-lg { font-size: 1rem; }
        .story-section .text-xl { font-size: 1.15rem; }
    }
    @media (prefers-reduced-motion: reduce) {
        .animate-fade-up, .ending-section { animation: none; opacity: 1; }
        .stat-number { transition: none; }
        .chapter-card .group-hover\:scale-105 { transform: none !important; }
    }
</style>

@endsection