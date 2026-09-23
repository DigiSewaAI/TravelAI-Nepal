{{-- PHASE 4E: Altitude Profile (Traveler Dashboard) --}}

@php
    $bookingService = $booking->service ?? null;
    $altitudePoints = [];

    if ($bookingService && $bookingService->itineraryDays->isNotEmpty()) {
        foreach ($bookingService->itineraryDays as $day) {
            $alt = $day->altitude_m;
            if ($alt === null || $alt === '') continue;
            $wp = $day->overnightWaypoint ?? $day->endWaypoint ?? $day->startWaypoint;
            $altitudePoints[] = [
                'day' => $day->day_number,
                'altitude' => (int) $alt,
                'name' => $wp->name ?? ('Day ' . $day->day_number),
            ];
        }
    }

    $validCount = count($altitudePoints);
    $showChart = $validCount >= 2;

    if ($showChart) {
        $altitudes = array_column($altitudePoints, 'altitude');
        $minAlt = min($altitudes);
        $maxAlt = max($altitudes);
        $range = max($maxAlt - $minAlt, 100);

        $svgW = 800; $svgH = 280;
        $padL = 70; $padR = 30; $padT = 30; $padB = 45;
        $plotW = $svgW - $padL - $padR;
        $plotH = $svgH - $padT - $padB;
        $stepX = $validCount > 1 ? $plotW / ($validCount - 1) : 0;

        $points = [];
        foreach ($altitudePoints as $i => $p) {
            $x = $padL + ($i * $stepX);
            $y = $padT + $plotH * (1 - ($p['altitude'] - $minAlt) / $range);
            $points[] = ['x' => round($x, 1), 'y' => round($y, 1), 'data' => $p];
        }
        $polylineStr = implode(' ', array_map(fn($pt) => $pt['x'] . ',' . $pt['y'], $points));
        $firstX = $points[0]['x']; $lastX = end($points)['x']; $bottomY = $padT + $plotH;
        $areaPath = "M {$firstX},{$bottomY} L " . $polylineStr . " L {$lastX},{$bottomY} Z";
    }
@endphp

@if($showChart)
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 md:p-6 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base md:text-lg font-bold text-gray-900 flex items-center gap-2">
            ⛰️ {{ __('messages.altitude_profile_heading') }}
        </h3>
        <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
            {{ $validCount }} {{ __('messages.days') }}
        </span>
    </div>

    <div class="overflow-x-auto">
        <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" preserveAspectRatio="xMidYMid meet"
             class="w-full h-auto min-w-[500px]" role="img"
             aria-label="{{ __('messages.altitude_profile_heading') }}">
            <defs>
                <linearGradient id="dashAltArea" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#10b981" stop-opacity="0.35"/>
                    <stop offset="100%" stop-color="#10b981" stop-opacity="0.02"/>
                </linearGradient>
            </defs>

            @for($i = 0; $i <= 3; $i++)
                @php
                    $ratio = $i / 3;
                    $altVal = $minAlt + $range * $ratio;
                    $yPos = $padT + $plotH * (1 - $ratio);
                @endphp
                <line x1="{{ $padL }}" y1="{{ $yPos }}" x2="{{ $svgW - $padR }}" y2="{{ $yPos }}"
                      stroke="#e5e7eb" stroke-width="1" stroke-dasharray="3,3"/>
                <text x="{{ $padL - 10 }}" y="{{ $yPos + 4 }}" text-anchor="end"
                      font-size="11" fill="#6b7280" font-family="Inter,sans-serif">
                    {{ round($altVal) }}m
                </text>
            @endfor

            <path d="{{ $areaPath }}" fill="url(#dashAltArea)"/>
            <polyline points="{{ $polylineStr }}" fill="none"
                      stroke="#10b981" stroke-width="3"
                      stroke-linecap="round" stroke-linejoin="round"/>

            @foreach($points as $pt)
                <g>
                    <title>Day {{ $pt['data']['day'] }} — {{ $pt['data']['name'] }}: {{ $pt['data']['altitude'] }}m</title>
                    <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="6"
                            fill="#ffffff" stroke="#059669" stroke-width="2.5"/>
                    <text x="{{ $pt['x'] }}" y="{{ $svgH - 18 }}" text-anchor="middle"
                          font-size="11" font-weight="600" fill="#4b5563" font-family="Inter,sans-serif">
                        D{{ $pt['data']['day'] }}
                    </text>
                    <text x="{{ $pt['x'] }}" y="{{ $pt['y'] - 12 }}" text-anchor="middle"
                          font-size="10" font-weight="700" fill="#059669" font-family="Inter,sans-serif">
                        {{ $pt['data']['altitude'] }}m
                    </text>
                </g>
            @endforeach
        </svg>
    </div>
</div>
@endif