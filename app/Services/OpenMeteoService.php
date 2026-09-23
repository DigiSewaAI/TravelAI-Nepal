<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenMeteoService
{
    const CACHE_TTL = 21600; // 6 hours
    const TIMEOUT   = 5;
    const ENDPOINT  = 'https://api.open-meteo.com/v1/forecast';

    /**
     * Fetch current weather for given coordinates.
     * Returns array with temperature/weather_code/wind_speed/humidity/time, or null on failure.
     */
    public function getWeatherForCoords(float $lat, float $lng): ?array
    {
        $key = 'weather:meteo:' . round($lat, 4) . ':' . round($lng, 4);

        return Cache::remember($key, self::CACHE_TTL, function () use ($lat, $lng) {
            try {
                $response = Http::timeout(self::TIMEOUT)
                    ->acceptJson()
                    ->get(self::ENDPOINT, [
                        'latitude'  => $lat,
                        'longitude' => $lng,
                        'current'   => 'temperature_2m,weather_code,wind_speed_10m,relative_humidity_2m',
                        'timezone'  => 'auto',
                    ]);

                if (! $response->successful()) {
                    Log::warning('OpenMeteo HTTP error', [
                        'lat'    => $lat,
                        'lng'    => $lng,
                        'status' => $response->status(),
                    ]);
                    return null;
                }

                $current = $response->json('current');
                if (! is_array($current)) {
                    return null;
                }

                return [
                    'temperature'  => $current['temperature_2m']            ?? null,
                    'weather_code' => $current['weather_code']              ?? null,
                    'wind_speed'   => $current['wind_speed_10m']            ?? null,
                    'humidity'     => $current['relative_humidity_2m']      ?? null,
                    'time'         => $current['time']                      ?? null,
                ];
            } catch (\Throwable $e) {
                Log::warning('OpenMeteo fetch exception', [
                    'lat'     => $lat,
                    'lng'     => $lng,
                    'message' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Fetch daily forecast (sunrise/sunset/daylight) for coordinates.
     */
    public function getDailyForecastForCoords(float $lat, float $lng, int $days = 7): ?array
    {
        $key = 'weather:meteo:daily:' . round($lat, 4) . ':' . round($lng, 4) . ':' . $days;

        return Cache::remember($key, self::CACHE_TTL, function () use ($lat, $lng, $days) {
            try {
                $response = Http::timeout(self::TIMEOUT)
                    ->acceptJson()
                    ->get(self::ENDPOINT, [
                        'latitude'      => $lat,
                        'longitude'     => $lng,
                        'daily'         => 'sunrise,sunset,daylight_duration',
                        'timezone'      => 'auto',
                        'forecast_days' => $days,
                    ]);

                if (! $response->successful()) {
                    Log::warning('OpenMeteo daily HTTP error', [
                        'lat'    => $lat,
                        'lng'    => $lng,
                        'status' => $response->status(),
                    ]);
                    return null;
                }

                $daily = $response->json('daily');
                if (! is_array($daily)) {
                    return null;
                }

                return [
                    'sunrise'           => $daily['sunrise'] ?? [],
                    'sunset'            => $daily['sunset'] ?? [],
                    'daylight_duration' => $daily['daylight_duration'] ?? [],
                ];
            } catch (\Throwable $e) {
                Log::warning('OpenMeteo daily fetch exception', [
                    'lat'     => $lat,
                    'lng'     => $lng,
                    'message' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Format ISO 8601 time string to friendly 12-hour format.
     * Example: "2026-09-23T05:42" → "5:42 AM"
     */
    public static function formatTime(?string $iso): ?string
    {
        if (! $iso) return null;
        try {
            return \Carbon\Carbon::parse($iso)->format('g:i A');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Format daylight duration (seconds) to "12h 36m".
     * Accepts float (Open-Meteo returns 43629.55) or int.
     * Rounds to nearest second for accuracy.
     */
    public static function formatDuration(float|int|null $seconds): ?string
    {
        if ($seconds === null || $seconds <= 0) return null;
        $s = (int) round($seconds);
        $h = intdiv($s, 3600);
        $m = intdiv($s % 3600, 60);
        return $h . 'h ' . str_pad((string) $m, 2, '0', STR_PAD_LEFT) . 'm';
    }

    /**
     * Map WMO weather code → category icon slug + human label.
     * Returns ['icon' => 'clear'|'cloud'|'rain'|'snow'|'fog'|'storm'|'unknown', 'label' => string]
     */
    public static function describeWmoCode(?int $code): array
    {
        if ($code === null) {
            return ['icon' => 'unknown', 'label' => 'Unknown'];
        }

        $map = [
            0  => ['icon' => 'clear',  'label' => 'Clear sky'],
            1  => ['icon' => 'clear',  'label' => 'Mainly clear'],
            2  => ['icon' => 'cloud',  'label' => 'Partly cloudy'],
            3  => ['icon' => 'cloud',  'label' => 'Overcast'],
            45 => ['icon' => 'fog',    'label' => 'Fog'],
            48 => ['icon' => 'fog',    'label' => 'Rime fog'],
            51 => ['icon' => 'rain',   'label' => 'Light drizzle'],
            53 => ['icon' => 'rain',   'label' => 'Moderate drizzle'],
            55 => ['icon' => 'rain',   'label' => 'Dense drizzle'],
            56 => ['icon' => 'rain',   'label' => 'Freezing drizzle'],
            57 => ['icon' => 'rain',   'label' => 'Freezing drizzle'],
            61 => ['icon' => 'rain',   'label' => 'Slight rain'],
            63 => ['icon' => 'rain',   'label' => 'Moderate rain'],
            65 => ['icon' => 'rain',   'label' => 'Heavy rain'],
            66 => ['icon' => 'rain',   'label' => 'Freezing rain'],
            67 => ['icon' => 'rain',   'label' => 'Freezing rain'],
            71 => ['icon' => 'snow',   'label' => 'Slight snow'],
            73 => ['icon' => 'snow',   'label' => 'Moderate snow'],
            75 => ['icon' => 'snow',   'label' => 'Heavy snow'],
            77 => ['icon' => 'snow',   'label' => 'Snow grains'],
            80 => ['icon' => 'rain',   'label' => 'Rain showers'],
            81 => ['icon' => 'rain',   'label' => 'Rain showers'],
            82 => ['icon' => 'rain',   'label' => 'Violent rain showers'],
            85 => ['icon' => 'snow',   'label' => 'Snow showers'],
            86 => ['icon' => 'snow',   'label' => 'Snow showers'],
            95 => ['icon' => 'storm',  'label' => 'Thunderstorm'],
            96 => ['icon' => 'storm',  'label' => 'Thunderstorm with hail'],
            99 => ['icon' => 'storm',  'label' => 'Thunderstorm with hail'],
        ];

        return $map[$code] ?? ['icon' => 'unknown', 'label' => 'Unknown'];
    }
}