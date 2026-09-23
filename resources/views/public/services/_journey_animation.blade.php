{{-- PROVIDER-ITINERARY-GLOBE-PHASE-3: Journey Animation (2D Map - Video Match) --}}
{{-- Summary server-rendered (always visible, even without JS) --}}

@if($service->isItineraryPublished() && $service->itineraryDays->isNotEmpty())

@php
    $journeyWaypoints = [];
    foreach ($service->itineraryDays as $day) {
        $wp = $day->overnightWaypoint ?? $day->endWaypoint ?? $day->startWaypoint;
        if (!$wp || !$wp->latitude || !$wp->longitude) {
            continue;
        }
        $journeyWaypoints[] = [
            'day'           => $day->day_number,
            'title'         => $day->title,
            'name'          => $wp->name,
            'lat'           => (float) $wp->latitude,
            'lng'           => (float) $wp->longitude,
            'altitude'      => $wp->altitude ?? $day->altitude_m ?? null,
            'distance'      => $day->distance_km,
            'time'          => $day->estimated_time_hours,
            'accommodation' => $day->accommodation,
        ];
    }
@endphp

@if(count($journeyWaypoints) >= 2)

@push('head')
<style>
    .journey-popup .leaflet-popup-content-wrapper {
        border-radius: 14px;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
        padding: 2px;
        border: 1px solid rgba(0, 0, 0, 0.04);
    }
    .journey-popup .leaflet-popup-content {
        margin: 12px 14px;
    }
    .journey-popup .leaflet-popup-tip {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }

    /* Fix: Prevent map from overriding sticky header */
    #journeyMapWrap {
        position: relative;
        z-index: 1;
    }

    /* Summary cards scrollbar */
    #journeySummaryScroll::-webkit-scrollbar {
        width: 5px;
    }
    #journeySummaryScroll::-webkit-scrollbar-track {
        background: transparent;
    }
    #journeySummaryScroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    #journeySummaryScroll::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Active summary card highlight */
    .journey-card-active {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%) !important;
        border-color: #93c5fd !important;
        box-shadow: 0 4px 12px -2px rgba(59, 130, 246, 0.2);
        transform: translateX(-2px);
    }

    /* Day progress bar shimmer */
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    .journey-progress-active {
        background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 50%, #3b82f6 100%);
        background-size: 200% 100%;
        animation: shimmer 2s linear infinite;
    }
</style>
@endpush

<section class="mt-8" id="journeyAnimationSection">
    {{-- Cinematic stage wrapper --}}
    <div class="max-w-6xl mx-auto bg-gradient-to-b from-slate-50 to-white border border-gray-200 rounded-3xl p-5 md:p-8 shadow-sm">

        {{-- ─── Header (fully centered) ─── --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-bold uppercase tracking-wider mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                Interactive Journey
            </div>

            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">
                🗺️ {{ __('messages.journey_animation_heading') ?? 'Journey Animation' }}
            </h2>
            <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                {{ __('messages.journey_animation_subtitle') ?? 'Watch your adventure unfold day by day' }}
            </p>

            {{-- Status badges --}}
            <div class="flex items-center justify-center gap-2 text-xs mt-4">
                <span class="px-3 py-1.5 rounded-full bg-white border border-gray-200 text-gray-600 font-semibold uppercase tracking-wider shadow-sm">
                    {{ __('messages.day') ?? 'Day' }}
                    <span id="journeyCurrentDay" class="text-blue-600">1</span>
                    <span class="text-gray-400">/ {{ count($journeyWaypoints) }}</span>
                </span>
                <span class="px-3 py-1.5 rounded-full bg-blue-100 text-blue-700 font-bold shadow-sm transition-all"
                      id="journeyStatus">Ready</span>
            </div>

            {{-- Progress bar --}}
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden mt-4 max-w-md mx-auto">
                <div id="journeyProgressBar"
                     class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full transition-all duration-500"
                     style="width: {{ (1 / count($journeyWaypoints)) * 100 }}%"></div>
            </div>
        </div>

        {{-- ─── Grid: Map + Summary ─── --}}
        <div id="journeyGrid" class="grid md:grid-cols-3 gap-4">

            {{-- Map Container --}}
            <div id="journeyMapWrap"
                 class="md:col-span-2 rounded-2xl overflow-hidden shadow-xl relative bg-gray-100 h-[300px] md:h-[440px] border border-gray-200">
                <div id="journeyMap" class="w-full h-full"></div>

                <div id="journeyMapLoading"
                     class="absolute inset-0 flex items-center justify-center text-gray-700 text-sm bg-gray-100/90 backdrop-blur-sm pointer-events-none z-[400]">
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-8 h-8 border-3 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                        <span>{{ __('messages.loading') ?? 'Loading map...' }}</span>
                    </div>
                </div>
            </div>

            {{-- Summary Panel --}}
            <div id="journeySummary"
                 class="md:col-span-1 bg-white rounded-2xl border border-gray-200 shadow-lg p-4 h-[300px] md:h-[440px] flex flex-col">

                <div class="flex items-center justify-between mb-3 flex-shrink-0 pb-3 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                        <span class="text-base">🗺️</span>
                        {{ count($journeyWaypoints) }}-Day Journey
                    </h3>
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                        Summary
                    </span>
                </div>

                <div id="journeySummaryScroll" class="flex-1 space-y-2 overflow-y-auto pr-1.5">
                    @foreach($journeyWaypoints as $idx => $wp)
                        <div class="journey-card bg-gray-50 hover:bg-blue-50 rounded-xl border border-gray-100 p-3 text-xs transition-all duration-300 cursor-pointer"
                             data-day-index="{{ $idx }}">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold text-[10px]">
                                    {{ $wp['day'] }}
                                </span>
                                <span class="text-gray-400 font-medium text-[11px]">
                                    {{ $wp['distance'] ? $wp['distance'] . ' km' : '—' }}
                                </span>
                            </div>
                            <div class="font-semibold text-gray-900 leading-snug text-[13px]">
                                {{ $wp['name'] }}
                            </div>
                            <div class="text-gray-500 text-[11px] mt-1.5 leading-snug flex flex-wrap gap-x-2 gap-y-0.5">
                                <span>⛰️ {{ $wp['altitude'] ? $wp['altitude'] . ' m' : 'N/A' }}</span>
                                <span class="text-gray-300">·</span>
                                <span>⏱️ {{ $wp['time'] ? $wp['time'] . ' hrs' : '—' }}</span>
                            </div>
                            @if($wp['accommodation'])
                                <div class="text-gray-500 text-[11px] mt-1 leading-snug truncate">
                                    🏨 {{ $wp['accommodation'] }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ─── Controls ─── --}}
        <div class="mt-5 flex items-center justify-center gap-3">
            <button type="button" id="journeyPrevBtn"
                    class="group inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold rounded-xl border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:border-gray-200 disabled:hover:bg-transparent">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Prev
            </button>

            <button type="button" id="journeyPlayBtn"
                    class="group inline-flex items-center gap-2 px-7 py-2.5 text-sm font-bold rounded-xl bg-gradient-to-b from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700 transition-all shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 active:scale-95">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                <span id="journeyPlayLabel">Replay</span>
            </button>

            <button type="button" id="journeyNextBtn"
                    class="group inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold rounded-xl border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:border-gray-200 disabled:hover:bg-transparent">
                Next
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>
</section>

@push('scripts')
<script>
(function () {
    'use strict';

    var JOURNEY = @json($journeyWaypoints);
    var NEPAL_CENTER = [28.3949, 84.1240];
    var ARC_POINTS = 40;
    var ARC_STEP_MS = 40;
    var PAUSE_AFTER_DROP_MS = 1600;

    // How many pixels below screen-center pin should land.
    // Popup opens above pin → this gives room above without cutting.
    var PIN_OFFSET_Y = 90;

    var map = null;
    var currentIndex = 0;
    var isPlaying = false;
    var arcTimer = null;
    var pauseTimer = null;

    var activePolyline = null;
    var pins = [];
    var summaryCards = [];

    var els = {
        container: document.getElementById('journeyMap'),
        loading:   document.getElementById('journeyMapLoading'),
        day:       document.getElementById('journeyCurrentDay'),
        status:    document.getElementById('journeyStatus'),
        play:      document.getElementById('journeyPlayBtn'),
        playLabel: document.getElementById('journeyPlayLabel'),
        prev:      document.getElementById('journeyPrevBtn'),
        next:      document.getElementById('journeyNextBtn'),
        progress:  document.getElementById('journeyProgressBar'),
    };

    if (!els.container) return;

    // ─────────── POPUP HTML ───────────
    function buildPopupHtml(wp) {
        var acc = wp.accommodation
            ? '<div style="display:flex;align-items:flex-start;gap:4px;margin-top:2px;"><span>🏨</span><span>' + wp.accommodation + '</span></div>'
            : '';

        return '<div style="font-family:Inter,system-ui,sans-serif;min-width:200px;">' +
            '<div style="display:inline-block;font-size:10px;font-weight:700;color:#2563eb;background:#eff6ff;padding:2px 8px;border-radius:999px;letter-spacing:0.05em;">DAY ' + wp.day + '</div>' +
            '<div style="font-size:14px;font-weight:700;color:#111827;margin-top:6px;line-height:1.35;">' +
                (wp.title || wp.name) +
            '</div>' +
            '<div style="height:1px;background:#e5e7eb;margin:8px 0;"></div>' +
            '<div style="font-size:12px;color:#4b5563;line-height:1.7;">' +
                '<div style="display:flex;align-items:flex-start;gap:5px;"><span>📍</span><span style="font-weight:600;color:#1f2937;">' + wp.name + '</span></div>' +
                '<div style="display:flex;align-items:center;gap:5px;"><span>⛰️</span><span>' + (wp.altitude ? wp.altitude + ' m' : 'N/A') + '</span></div>' +
                '<div style="display:flex;align-items:center;gap:5px;"><span>📏</span><span>' + (wp.distance ? wp.distance + ' km' : '—') + '</span></div>' +
                '<div style="display:flex;align-items:center;gap:5px;"><span>⏱️</span><span>' + (wp.time ? wp.time + ' hrs' : '—') + '</span></div>' +
                acc +
            '</div>' +
        '</div>';
    }

    // ─────────── INIT MAP ───────────
    function initJourneyMap() {
        map = L.map(els.container, {
            center: NEPAL_CENTER,
            zoom: 7,
            zoomControl: true,
            scrollWheelZoom: false,
            attributionControl: true,
            zoomAnimation: true,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            subdomains: 'abc',
            maxZoom: 19,
        }).addTo(map);

        var bounds = [];

        JOURNEY.forEach(function (wp, i) {
            var color = (i === 0) ? '#22c55e'
                      : (i === JOURNEY.length - 1) ? '#ef4444'
                      : '#3b82f6';

            var marker = L.circleMarker([wp.lat, wp.lng], {
                radius: 8,
                fillColor: color,
                color: '#ffffff',
                weight: 2.5,
                opacity: 1,
                fillOpacity: 0.95,
            }).addTo(map);

            // ─── Popup with proper autoPan (fixes cut-off) ───
            marker.bindPopup(buildPopupHtml(wp), {
                offset: [0, -12],
                closeButton: false,
                autoPan: true,
                autoPanPaddingTopLeft: L.point(60, 170),   // 170px top room
                autoPanPaddingBottomRight: L.point(60, 50),
                maxWidth: 260,
                minWidth: 220,
                className: 'journey-popup',
                autoClose: false,
                closeOnClick: false,
                keepInView: true,
            });

            pins.push(marker);
            bounds.push([wp.lat, wp.lng]);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }

        if (els.loading) els.loading.classList.add('hidden');

        window.addEventListener('resize', function () {
            if (map) map.invalidateSize();
        });
    }

    // ─────────── FLY TO PIN (with popup-room offset) ───────────
    // Centers the map so the pin appears ~PIN_OFFSET_Y px BELOW center.
    // That leaves room above the pin for the popup to open without being cut.
    function flyToPinWithRoom(lat, lng, zoom, duration) {
        if (!map) return;
        var z = zoom || 11;
        var pinPoint = map.project([lat, lng], z);
        var centerPoint = pinPoint.subtract([0, PIN_OFFSET_Y]); // shift view north
        var newCenter = map.unproject(centerPoint, z);
        map.flyTo(newCenter, z, { duration: duration || 1.4 });
    }

    // ─────────── CURVED PATH ───────────
    function buildCurvedPath(start, end, points) {
        var midLat = (start.lat + end.lat) / 2;
        var midLng = (start.lng + end.lng) / 2;

        var dx = end.lng - start.lng;
        var dy = end.lat - start.lat;
        var distance = Math.sqrt(dx * dx + dy * dy);
        var offset = Math.min(distance * 0.18, 0.5);

        var ctrlLat = midLat + dx * offset;
        var ctrlLng = midLng - dy * offset;

        var path = [];
        for (var i = 0; i <= points; i++) {
            var t = i / points;
            var lat = Math.pow(1 - t, 2) * start.lat
                    + 2 * (1 - t) * t * ctrlLat
                    + Math.pow(t, 2) * end.lat;
            var lng = Math.pow(1 - t, 2) * start.lng
                    + 2 * (1 - t) * t * ctrlLng
                    + Math.pow(t, 2) * end.lng;
            path.push([lat, lng]);
        }
        return path;
    }

    // ─────────── UPDATE PANEL + POPUP + SUMMARY HIGHLIGHT ───────────
    // options.scroll (default false) — only scroll when explicitly requested.
    // Prevents auto-scroll on page load (boot).
    function updatePanel(index, options) {
        options = options || {};
        var shouldScroll = (options.scroll === true);

        var wp = JOURNEY[index];
        if (!wp) return;

        if (els.day) els.day.textContent = wp.day;
        if (els.status) els.status.textContent = 'Playing';

        if (els.prev) els.prev.disabled = (index === 0);
        if (els.next) els.next.disabled = (index === JOURNEY.length - 1);

        // Progress bar
        if (els.progress) {
            var pct = ((index + 1) / JOURNEY.length) * 100;
            els.progress.style.width = pct + '%';
        }

        // Highlight active summary card (+ optional scroll)
        summaryCards = document.querySelectorAll('.journey-card');
        summaryCards.forEach(function (card, i) {
            if (i === index) {
                card.classList.add('journey-card-active');
                if (shouldScroll) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            } else {
                card.classList.remove('journey-card-active');
            }
        });

        // Close all popups, open only current
        pins.forEach(function (p, i) {
            if (i !== index) p.closePopup();
        });
        if (pins[index]) {
            pins[index].openPopup();
        }
    }

    // ─────────── SHOW SUMMARY ───────────
    function showSummary() {
        // Summary is server-rendered & always visible — just resize map
        if (map) setTimeout(function () { map.invalidateSize(); }, 300);
    }

    // ─────────── RESET TO DAY 1 ───────────
    function resetJourney() {
        currentIndex = 0;
        isPlaying = false;
        clearArc();

        if (els.status) els.status.textContent = 'Ready';
        if (els.playLabel) els.playLabel.textContent = 'Replay';
        if (els.prev) els.prev.disabled = true;
        if (els.next) els.next.disabled = false;

        // Reset progress bar
        if (els.progress) {
            els.progress.style.width = ((1 / JOURNEY.length) * 100) + '%';
        }

        var bounds = JOURNEY.map(function (wp) { return [wp.lat, wp.lng]; });
        if (map && bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }

        pins.forEach(function (p) { p.closePopup(); });
        if (pins[0]) pins[0].openPopup();

        // Reset summary highlight to first card (no scroll on reset)
        summaryCards = document.querySelectorAll('.journey-card');
        summaryCards.forEach(function (card, i) {
            if (i === 0) card.classList.add('journey-card-active');
            else card.classList.remove('journey-card-active');
        });

        if (map) setTimeout(function () { map.invalidateSize(); }, 300);
    }

    // ─────────── CLEAR ARC ───────────
    function clearArc() {
        if (activePolyline && map) {
            map.removeLayer(activePolyline);
            activePolyline = null;
        }
    }

    // ─────────── REVEAL ARC ───────────
    function revealArc(fromIndex, toIndex, onComplete) {
        var start = JOURNEY[fromIndex];
        var end = JOURNEY[toIndex];
        var fullPath = buildCurvedPath(start, end, ARC_POINTS);

        clearArc();
        activePolyline = L.polyline([fullPath[0]], {
            color: '#2563eb',
            weight: 4,
            opacity: 0.95,
            lineCap: 'round',
            lineJoin: 'round',
        }).addTo(map);

        var revealedCount = 1;

        arcTimer = setInterval(function () {
            revealedCount++;
            if (revealedCount > fullPath.length) {
                revealedCount = fullPath.length;
                clearInterval(arcTimer);
                arcTimer = null;
                if (onComplete) onComplete();
                return;
            }
            activePolyline.setLatLngs(fullPath.slice(0, revealedCount));
        }, ARC_STEP_MS);
    }

    // ─────────── MAIN STEP ───────────
    function stepToDay(targetIndex) {
        if (!isPlaying) return;

        if (targetIndex >= JOURNEY.length) {
            stopJourney();
            if (els.status) els.status.textContent = 'Complete';
            if (els.playLabel) els.playLabel.textContent = 'Replay';
            showSummary();
            return;
        }

        currentIndex = targetIndex;
        var wp = JOURNEY[targetIndex];

        // ─── Step 1: Fly to pin WITH popup-room offset ───
        flyToPinWithRoom(wp.lat, wp.lng, 11, 1.4);
        if (els.status) els.status.textContent = 'Day ' + wp.day;

        // ─── Step 2: After zoom → open popup ───
        pauseTimer = setTimeout(function () {
            if (!isPlaying) return;

            // ✅ scroll only during active playback
            updatePanel(targetIndex, { scroll: true });

            var nextIndex = targetIndex + 1;
            if (nextIndex >= JOURNEY.length) {
                stopJourney();
                if (els.status) els.status.textContent = 'Complete';
                if (els.playLabel) els.playLabel.textContent = 'Replay';
                showSummary();
                return;
            }

            var end = JOURNEY[nextIndex];

            // ─── Step 3: Zoom out to fit arc ───
            var midLat = (wp.lat + end.lat) / 2;
            var midLng = (wp.lng + end.lng) / 2;
            if (map) {
                map.flyTo([midLat, midLng], 9, { duration: 0.9 });
            }

            // ─── Step 4: Grow arc ───
            pauseTimer = setTimeout(function () {
                if (!isPlaying) return;

                revealArc(targetIndex, nextIndex, function () {
                    if (!isPlaying) return;

                    pauseTimer = setTimeout(function () {
                        stepToDay(nextIndex);
                    }, PAUSE_AFTER_DROP_MS);
                });
            }, 900);
        }, 1400);
    }

    // ─────────── PLAY / PAUSE / STOP ───────────
    function playJourney() {
        if (isPlaying) return;
        isPlaying = true;
        if (els.playLabel) els.playLabel.textContent = 'Pause';
        if (els.status) els.status.textContent = 'Playing';

        if (currentIndex >= JOURNEY.length - 1) {
            currentIndex = 0;
            clearArc();
            // ✅ user explicitly started playback → scroll is fine
            updatePanel(0, { scroll: true });
        }

        stepToDay(currentIndex);
    }

    function pauseJourney() {
        isPlaying = false;
        if (arcTimer) { clearInterval(arcTimer); arcTimer = null; }
        if (pauseTimer) { clearTimeout(pauseTimer); pauseTimer = null; }
        if (els.playLabel) els.playLabel.textContent = 'Play';
        if (els.status) els.status.textContent = 'Paused';
    }

    function stopJourney() {
        isPlaying = false;
        if (arcTimer) { clearInterval(arcTimer); arcTimer = null; }
        if (pauseTimer) { clearTimeout(pauseTimer); pauseTimer = null; }
    }

    // ─────────── CONTROLS ───────────
    els.play.addEventListener('click', function () {
        if (isPlaying) pauseJourney();
        else playJourney();
    });

    els.prev.addEventListener('click', function () {
        if (isPlaying) pauseJourney();
        if (currentIndex > 0) {
            currentIndex--;
            clearArc();
            // ✅ user clicked → scroll OK
            updatePanel(currentIndex, { scroll: true });
            if (els.status) els.status.textContent = 'Ready';

            var wp = JOURNEY[currentIndex];
            if (wp) flyToPinWithRoom(wp.lat, wp.lng, 11, 0.8);
        }
    });

    els.next.addEventListener('click', function () {
        if (isPlaying) pauseJourney();
        if (currentIndex < JOURNEY.length - 1) {
            currentIndex++;
            clearArc();
            // ✅ user clicked → scroll OK
            updatePanel(currentIndex, { scroll: true });
            if (els.status) els.status.textContent = 'Ready';

            var wp = JOURNEY[currentIndex];
            if (wp) flyToPinWithRoom(wp.lat, wp.lng, 11, 0.8);
        }
    });

    // ─────────── SUMMARY CARD CLICK ───────────
    document.querySelectorAll('.journey-card').forEach(function (card) {
        card.addEventListener('click', function () {
            var idx = parseInt(card.getAttribute('data-day-index'), 10);
            if (isNaN(idx)) return;

            if (isPlaying) pauseJourney();
            currentIndex = idx;
            clearArc();
            // ✅ user clicked → scroll OK
            updatePanel(idx, { scroll: true });
            if (els.status) els.status.textContent = 'Ready';

            var wp = JOURNEY[idx];
            if (wp) flyToPinWithRoom(wp.lat, wp.lng, 11, 1.0);
        });
    });

    // ─────────── BOOT ───────────
    function boot() {
        if (typeof L === 'undefined') {
            setTimeout(boot, 200);
            return;
        }
        initJourneyMap();
        // ✅ NO scroll on initial render — page stays at top
        updatePanel(0, { scroll: false });
    }

    if (document.readyState === 'complete') boot();
    else window.addEventListener('load', boot);

})();
</script>
@endpush

@endif
@endif