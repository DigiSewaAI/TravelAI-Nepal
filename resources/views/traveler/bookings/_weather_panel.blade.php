{{-- PHASE 4E: Weather Panel (Traveler Dashboard) --}}
{{-- Per-booking weather with sunrise/sunset --}}

@php
    $bookingService = $booking->service ?? null;
    $weatherService = app(\App\Services\OpenMeteoService::class);
    $weatherRows = [];

    if ($bookingService && $bookingService->itineraryDays->isNotEmpty()) {
        foreach ($bookingService->itineraryDays as $day) {
            $wp = $day->overnightWaypoint ?? $day->endWaypoint ?? $day->startWaypoint;
            if (!$wp || !$wp->latitude || !$wp->longitude) continue;

            $weather = $weatherService->getWeatherForCoords((float) $wp->latitude, (float) $wp->longitude);
            $daily = $weatherService->getDailyForecastForCoords((float) $wp->latitude, (float) $wp->longitude, 1);
            $wmo = $weather ? \App\Services\OpenMeteoService::describeWmoCode($weather['weather_code'] ?? null) : null;

            $sunrise = $sunset = $daylight = null;
            if ($daily && !empty($daily['sunrise'][0])) {
                $sunrise = \App\Services\OpenMeteoService::formatTime($daily['sunrise'][0]);
                $sunset = \App\Services\OpenMeteoService::formatTime($daily['sunset'][0] ?? null);
                $daylight = \App\Services\OpenMeteoService::formatDuration($daily['daylight_duration'][0] ?? null);
            }

            $weatherRows[] = [
                'day' => $day->day_number,
                'location' => $wp->name,
                'weather' => $weather,
                'wmo' => $wmo,
                'sunrise' => $sunrise,
                'sunset' => $sunset,
                'daylight' => $daylight,
            ];
        }
    }
@endphp

@if(count($weatherRows) > 0)
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 md:p-6 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base md:text-lg font-bold text-gray-900 flex items-center gap-2">
            🌤️ {{ __('messages.weather_heading') }}
        </h3>
        <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
            {{ count($weatherRows) }} {{ __('messages.days') }}
        </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach($weatherRows as $row)
            <div class="bg-gray-50 rounded-xl border border-gray-100 p-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-sky-100 text-sky-700 font-bold text-[10px]">
                            {{ $row['day'] }}
                        </span>
                        <span class="text-[11px] font-semibold text-gray-500 uppercase">
                            {{ __('messages.day') }} {{ $row['day'] }}
                        </span>
                    </span>
                    @if($row['weather'])
                        <span class="text-lg font-bold text-gray-900">
                            {{ round($row['weather']['temperature']) }}<span class="text-xs font-normal text-gray-400">°C</span>
                        </span>
                    @endif
                </div>

                <div class="font-semibold text-gray-900 text-sm truncate mb-1">
                    {{ $row['location'] }}
                </div>

                @if($row['wmo'])
                    <div class="text-gray-500 text-[11px] mb-2">{{ $row['wmo']['label'] }}</div>
                @endif

                @if($row['sunrise'] || $row['sunset'])
                    <div class="flex items-center justify-between text-[11px] text-gray-500 pt-2 border-t border-gray-200">
                        @if($row['sunrise'])
                            <span>🌅 {{ $row['sunrise'] }}</span>
                        @endif
                        @if($row['sunset'])
                            <span>🌇 {{ $row['sunset'] }}</span>
                        @endif
                    </div>
                @endif

                @if($row['daylight'])
                    <div class="text-[10px] text-gray-400 text-center mt-1">
                        ☀️ {{ $row['daylight'] }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif