<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openweathermap.org/data/2.5/weather';

    public function __construct()
    {
        $this->apiKey = env('OPENWEATHER_API_KEY');
    }

    /**
     * Get weather by city name (for snapshot)
     */
    public function getWeatherForCity($city, $country = 'NP')
    {
        $cacheKey = "weather_{$city}_{$country}";
        return Cache::remember($cacheKey, 900, function () use ($city, $country) {
            try {
                $response = Http::get($this->baseUrl, [
                    'q' => "{$city},{$country}",
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    // ✅ Check if API returned valid data
                    if (isset($data['cod']) && $data['cod'] != 200) {
                        return null;
                    }
                    return [
                        'city' => $data['name'] ?? $city,
                        'temp' => round($data['main']['temp'] ?? 0),
                        'feels_like' => round($data['main']['feels_like'] ?? 0),
                        'humidity' => $data['main']['humidity'] ?? 0,
                        'condition' => $data['weather'][0]['description'] ?? 'N/A',
                        'icon' => $data['weather'][0]['icon'] ?? '01d',
                        'wind_speed' => $data['wind']['speed'] ?? 0,
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Weather API Error: ' . $e->getMessage());
            }
            return null;
        });
    }

    /**
     * Get weather by coordinates (for search)
     */
    public function getWeatherByCoords($lat, $lon)
{
    $cacheKey = "weather_coords_{$lat}_{$lon}";
    return Cache::remember($cacheKey, 900, function () use ($lat, $lon) {
        try {
            $response = Http::timeout(10)->get($this->baseUrl, [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $this->apiKey,
                'units' => 'metric',
            ]);

            // ✅ Log the status
            \Log::info('Weather API status: ' . $response->status());
            \Log::info('Weather API body: ' . substr($response->body(), 0, 200));

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['cod']) && $data['cod'] != 200) {
                    \Log::warning('Weather API error: ' . json_encode($data));
                    return null;
                }
                return [
                    'temp' => round($data['main']['temp'] ?? 0),
                    'feels_like' => round($data['main']['feels_like'] ?? 0),
                    'humidity' => $data['main']['humidity'] ?? 0,
                    'condition' => $data['weather'][0]['description'] ?? 'N/A',
                    'icon' => $data['weather'][0]['icon'] ?? '01d',
                    'wind_speed' => $data['wind']['speed'] ?? 0,
                ];
            } else {
                \Log::error('Weather API failed: ' . $response->status() . ' - ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            \Log::error('Weather API Exception: ' . $e->getMessage());
            return null;
        }
    });
}

    /**
     * Get weather context for incident (simple summary)
     */
    public function getWeatherContext($lat, $lon)
    {
        $weather = $this->getWeatherByCoords($lat, $lon);
        if (!$weather) return null;
        
        $condition = $weather['condition'] ?? '';
        $temp = $weather['temp'] ?? 0;
        
        $context = "{$temp}°C";
        if (str_contains(strtolower($condition), 'rain')) {
            $context .= " · Heavy rain risk";
        } elseif (str_contains(strtolower($condition), 'snow')) {
            $context .= " · Snowfall";
        } elseif ($temp > 30) {
            $context .= " · Hot";
        }
        return $context;
    }
}