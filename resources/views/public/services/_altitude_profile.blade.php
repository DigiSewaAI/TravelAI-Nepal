{{-- PHASE 4B: Altitude Profile Chart --}}
{{-- Inline SVG line chart — day-by-day altitude trend --}}

@if($service->isItineraryPublished() && $service->itineraryDays->isNotEmpty())

@php
    $altitudePoints = [];
    foreach ($service->itineraryDays as $day) {
        $alt = $day->altitude_m;
        if ($alt === null || $alt === '') continue;
        $wp = $day->overnightWaypoint ?? $day->endWaypoint ?? $day->startWaypoint;
        $altitudePoints[] = [
            'day'      => $day->day_number,
            'altitude' => (int) $alt,
            'name'     => $wp->name ?? ('Day ' . $day->day_number),
        ];
    }

    $validCount = count($altitudePoints);
    $showChart = $validCount >= 2;

    if ($showChart) {
        $altitudes = array_column($altitudePoints, 'altitude');
        $minAlt = min($altitudes);
        $maxAlt = max($altitudes);
        $range = max($maxAlt - $minAlt, 100);

        $svgW = 800;
        $svgH = 320;
        $padL = 70;
        $padR = 30;
        $padT = 40;
        $padB = 50;

        $plotW = $svgW - $padL - $padR;
        $plotH = $svgH - $padT - $padB;

        $stepX = $validCount > 1 ? $plotW / ($validCount - 1) : 0;
        $points = [];
        foreach ($altitudePoints as $i => $p) {
            $x = $padL + ($i * $stepX);
            $yRatio = ($p['altitude'] - $minAlt) / $range;
            $y = $padT + $plotH * (1 - $yRatio);
            $points[] = ['x' => round($x, 1), 'y' => round($y, 1), 'data' => $p];
        }

        $polylineStr = implode(' ', array_map(fn($pt) => $pt['x'] . ',' . $pt['y'], $points));

        $firstX = $points[0]['x'];
        $lastX = end($points)['x'];
        $bottomY = $padT + $plotH;
        $areaPath = "M {$firstX},{$bottomY} L " . $polylineStr . " L {$lastX},{$bottomY} Z";

        $yTicks = [];
        for ($i = 0; $i <= 3; $i++) {
            $ratio = $i / 3;
            $altVal = $minAlt + $range * $ratio;
            $yPos = $padT + $plotH * (1 - $ratio);
            $yTicks[] = ['y' => round($yPos, 1), 'label' => round($altVal) . ' m'];
        }
    }
@endphp

@if($showChart)
<section class="mt-8" id="altitudeProfileSection">
    <div class="max-w-6xl mx-auto bg-gradient-to-b from-emerald-50 to-white border border-gray-200 rounded-3xl p-5 md:p-8 shadow-sm">

        {{-- Header --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold uppercase tracking-wider mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Elevation Trend
            </div>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">
                ⛰️ {{ __('messages.altitude_profile_heading') }}
            </h2>
            <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                {{ __('messages.altitude_profile_subtitle') }}
            </p>
        </div>

        {{-- Chart --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 md:p-6">
            <div class="w-full overflow-x-auto">
                <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}"
                     preserveAspectRatio="xMidYMid meet"
                     class="w-full h-auto min-w-[500px]"
                     role="img"
                     aria-label="{{ __('messages.altitude_profile_heading') }}">

                    <defs>
                        <linearGradient id="altAreaGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#10b981" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#10b981" stop-opacity="0.02"/>
                        </linearGradient>
                        <linearGradient id="altLineGrad" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0%" stop-color="#059669"/>
                            <stop offset="100%" stop-color="#10b981"/>
                        </linearGradient>
                    </defs>

                    @foreach($yTicks as $tick)
                        <line x1="{{ $padL }}" y1="{{ $tick['y'] }}"
                              x2="{{ $svgW - $padR }}" y2="{{ $tick['y'] }}"
                              stroke="#e5e7eb" stroke-width="1" stroke-dasharray="3,3"/>
                        <text x="{{ $padL - 10 }}" y="{{ $tick['y'] + 4 }}"
                              text-anchor="end"
                              font-size="11"
                              fill="#6b7280"
                              font-family="Inter, system-ui, sans-serif">
                            {{ $tick['label'] }}
                        </text>
                    @endforeach

                    <path d="{{ $areaPath }}" fill="url(#altAreaGrad)"/>

                    <polyline points="{{ $polylineStr }}"
                              fill="none"
                              stroke="url(#altLineGrad)"
                              stroke-width="3"
                              stroke-linecap="round"
                              stroke-linejoin="round"/>

                    @foreach($points as $pt)
                        <g class="alt-point" style="cursor:pointer;">
                            <title>Day {{ $pt['data']['day'] }} — {{ $pt['data']['name'] }}: {{ $pt['data']['altitude'] }}m</title>

                            <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="14" fill="transparent"/>

                            <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="6"
                                    fill="#ffffff"
                                    stroke="#059669"
                                    stroke-width="2.5"/>

                            <text x="{{ $pt['x'] }}" y="{{ $svgH - 20 }}"
                                  text-anchor="middle"
                                  font-size="11"
                                  font-weight="600"
                                  fill="#4b5563"
                                  font-family="Inter, system-ui, sans-serif">
                                D{{ $pt['data']['day'] }}
                            </text>

                            <text x="{{ $pt['x'] }}" y="{{ $pt['y'] - 14 }}"
                                  text-anchor="middle"
                                  font-size="10"
                                  font-weight="700"
                                  fill="#059669"
                                  font-family="Inter, system-ui, sans-serif">
                                {{ $pt['data']['altitude'] }}m
                            </text>
                        </g>
                    @endforeach
                </svg>
            </div>

            {{-- Legend --}}
            <div class="mt-4 flex flex-wrap items-center justify-center gap-3 text-xs text-gray-500">
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block w-4 h-0.5 rounded" style="background:#10b981;"></span>
                    {{ __('messages.altitude_profile_heading') }}
                </span>
                <span class="text-gray-300">·</span>
                <span>{{ $validCount }} {{ __('messages.days') }}</span>
                <span class="text-gray-300">·</span>
                <span>Min: {{ $minAlt }}m · Max: {{ $maxAlt }}m</span>
            </div>
        </div>
    </div>
</section>
@endif

@endif