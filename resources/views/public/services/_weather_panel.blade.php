{{-- PHASE 4A + 4D: Weather Panel with Sunrise/Sunset --}}
{{-- Compact weather list per itinerary day's overnight waypoint --}}

@if($service->isItineraryPublished() && $service->itineraryDays->isNotEmpty())

@php
    $weatherService = app(\App\Services\OpenMeteoService::class);
    $weatherRows = [];

    foreach ($service->itineraryDays as $day) {
        $wp = $day->overnightWaypoint ?? $day->endWaypoint ?? $day->startWaypoint;
        if (!$wp || !$wp->latitude || !$wp->longitude) {
            continue;
        }

        $weather = $weatherService->getWeatherForCoords(
            (float) $wp->latitude,
            (float) $wp->longitude
        );

        $daily = $weatherService->getDailyForecastForCoords(
            (float) $wp->latitude,
            (float) $wp->longitude,
            1
        );

        $wmo = $weather
            ? \App\Services\OpenMeteoService::describeWmoCode($weather['weather_code'] ?? null)
            : null;

        $sunrise = null;
        $sunset = null;
        $daylight = null;

        if ($daily && !empty($daily['sunrise'][0])) {
            $sunrise = \App\Services\OpenMeteoService::formatTime($daily['sunrise'][0]);
            $sunset = \App\Services\OpenMeteoService::formatTime($daily['sunset'][0] ?? null);
            $daylight = \App\Services\OpenMeteoService::formatDuration($daily['daylight_duration'][0] ?? null);
        }

        $weatherRows[] = [
            'day'      => $day->day_number,
            'location' => $wp->name,
            'weather'  => $weather,
            'wmo'      => $wmo,
            'sunrise'  => $sunrise,
            'sunset'   => $sunset,
            'daylight' => $daylight,
        ];
    }
@endphp

@if(count($weatherRows) > 0)
<section class="mt-8" id="weatherPanelSection">
    <div class="max-w-6xl mx-auto bg-gradient-to-b from-sky-50 to-white border border-gray-200 rounded-3xl p-5 md:p-8 shadow-sm">

        {{-- Header --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-50 text-sky-700 text-[11px] font-bold uppercase tracking-wider mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                Live Weather
            </div>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">
                🌤️ {{ __('messages.weather_heading') }}
            </h2>
            <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                {{ count($weatherRows) }}-{{ __('messages.days') }} weather for overnight stops
            </p>
        </div>

        {{-- Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($weatherRows as $row)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-sky-100 text-sky-700 font-bold text-[10px] flex-shrink-0">
                                    {{ $row['day'] }}
                                </span>
                                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                                    {{ __('messages.day') }} {{ $row['day'] }}
                                </span>
                            </div>
                            <div class="font-semibold text-gray-900 text-sm truncate">
                                {{ $row['location'] }}
                            </div>

                            @if($row['weather'] && $row['wmo'])
                                <div class="text-gray-500 text-[11px] mt-1.5 truncate">
                                    {{ $row['wmo']['label'] }}
                                </div>
                            @else
                                <div class="text-gray-400 text-[11px] mt-1.5 italic">
                                    {{ __('messages.weather_unavailable') }}
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col items-end flex-shrink-0">
                            @if($row['weather'])
                                @php $iconSlug = $row['wmo']['icon'] ?? 'unknown'; @endphp

                                <div class="w-10 h-10 flex items-center justify-center text-sky-500">
                                    @switch($iconSlug)
                                        @case('clear')
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <circle cx="12" cy="12" r="4"/>
                                                <path stroke-linecap="round" d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                                            </svg>
                                            @break
                                        @case('cloud')
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h10a4 4 0 000-8 6 6 0 00-11.5-1.5A4 4 0 003 15z"/>
                                            </svg>
                                            @break
                                        @case('rain')
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13a4 4 0 004 4h10a4 4 0 000-8 6 6 0 00-11.5-1.5A4 4 0 003 13z"/>
                                                <path stroke-linecap="round" d="M8 19l-1 2M12 19l-1 2M16 19l-1 2"/>
                                            </svg>
                                            @break
                                        @case('snow')
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13a4 4 0 004 4h10a4 4 0 000-8 6 6 0 00-11.5-1.5A4 4 0 003 13z"/>
                                                <circle cx="8" cy="20" r="0.6" fill="currentColor"/>
                                                <circle cx="12" cy="21" r="0.6" fill="currentColor"/>
                                                <circle cx="16" cy="20" r="0.6" fill="currentColor"/>
                                            </svg>
                                            @break
                                        @case('fog')
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" d="M4 8h16M4 12h16M4 16h16"/>
                                            </svg>
                                            @break
                                        @case('storm')
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13a4 4 0 004 4h10a4 4 0 000-8 6 6 0 00-11.5-1.5A4 4 0 003 13z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 17l-2 4 4-1-3 4"/>
                                            </svg>
                                            @break
                                        @default
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <circle cx="12" cy="12" r="9"/>
                                                <path stroke-linecap="round" d="M9.5 9.5h.01M14.5 9.5h.01M8.5 15c1-1 2.5-1.5 3.5-1.5s2.5.5 3.5 1.5"/>
                                            </svg>
                                    @endswitch
                                </div>

                                @if($row['weather']['temperature'] !== null)
                                    <div class="text-2xl font-bold text-gray-900 leading-none mt-1">
                                        {{ round($row['weather']['temperature']) }}<span class="text-sm font-normal text-gray-400">°C</span>
                                    </div>
                                @endif

                                <div class="text-[10px] text-gray-400 mt-1 flex flex-col items-end gap-0.5">
                                    @if($row['weather']['wind_speed'] !== null)
                                        <span>💨 {{ round($row['weather']['wind_speed']) }} km/h</span>
                                    @endif
                                    @if($row['weather']['humidity'] !== null)
                                        <span>💧 {{ $row['weather']['humidity'] }}%</span>
                                    @endif
                                </div>
                            @else
                                <div class="w-10 h-10 flex items-center justify-center text-gray-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path stroke-linecap="round" d="M9.5 9.5h.01M14.5 9.5h.01M8.5 15c1-1 2.5-1.5 3.5-1.5s2.5.5 3.5 1.5"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Phase 4D — Sunrise/Sunset row --}}
                    @if($row['sunrise'] || $row['sunset'] || $row['daylight'])
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center justify-between text-[11px] text-gray-500">
                                @if($row['sunrise'])
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-amber-500">🌅</span>
                                        <span class="font-medium">{{ __('messages.sunrise') }}</span>
                                        <span class="text-gray-700 font-semibold">{{ $row['sunrise'] }}</span>
                                    </span>
                                @endif
                                @if($row['sunset'])
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-orange-500">🌇</span>
                                        <span class="font-medium">{{ __('messages.sunset') }}</span>
                                        <span class="text-gray-700 font-semibold">{{ $row['sunset'] }}</span>
                                    </span>
                                @endif
                            </div>
                            @if($row['daylight'])
                                <div class="mt-1.5 text-[10px] text-gray-400 text-center">
                                    ☀️ {{ __('messages.daylight') }}: {{ $row['daylight'] }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="text-center mt-4 text-[11px] text-gray-400">
            {{ __('messages.weather_updated_at') }}: {{ now()->format('H:i') }} ·
            Data: <a href="https://open-meteo.com" target="_blank" rel="noopener" class="underline hover:text-sky-600">Open-Meteo</a>
        </div>
    </div>
</section>
@endif

@endif