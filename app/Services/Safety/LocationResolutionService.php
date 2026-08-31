<?php

namespace App\Services\Safety;

use App\Models\Location;
use App\Models\Waypoint;
use App\Models\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationResolutionService
{
    protected $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = config('safety.cache.geocode_ttl', 604800);
    }

    public function resolve(string $locationName): ?array
    {
        $cacheKey = 'geocode_' . md5($locationName);
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        // 1. Try existing Location model
        $existingLocation = $this->findInExistingLocations($locationName);
        if ($existingLocation) {
            $result = [
                'latitude' => $existingLocation->latitude,
                'longitude' => $existingLocation->longitude,
                'district' => $existingLocation->state,
                'province' => $existingLocation->country,
                'source' => 'existing_location',
            ];
            Cache::put($cacheKey, $result, $this->cacheTtl);
            return $result;
        }

        // 2. Try Waypoint
        $waypoint = $this->findInWaypoints($locationName);
        if ($waypoint) {
            $result = [
                'latitude' => $waypoint->latitude,
                'longitude' => $waypoint->longitude,
                'district' => null,
                'province' => null,
                'source' => 'waypoint',
            ];
            Cache::put($cacheKey, $result, $this->cacheTtl);
            return $result;
        }

        // 3. Try Route
        $route = $this->findInRoutes($locationName);
        if ($route && $route->segments->isNotEmpty()) {
            $firstSegment = $route->segments->first();
            $result = [
                'latitude' => $firstSegment->fromWaypoint?->latitude,
                'longitude' => $firstSegment->fromWaypoint?->longitude,
                'district' => null,
                'province' => null,
                'source' => 'route',
            ];
            Cache::put($cacheKey, $result, $this->cacheTtl);
            return $result;
        }

        // 4. Geocoding (Nominatim)
        try {
            $geocoded = $this->geocode($locationName);
            if ($geocoded) {
                Cache::put($cacheKey, $geocoded, $this->cacheTtl);
                return $geocoded;
            }
        } catch (\Exception $e) {
            Log::warning('Geocoding failed', ['location' => $locationName, 'error' => $e->getMessage()]);
        }

        // 5. Fuzzy match
        $fuzzy = $this->fuzzyMatch($locationName);
        if ($fuzzy) {
            Cache::put($cacheKey, $fuzzy, $this->cacheTtl);
            return $fuzzy;
        }

        return null;
    }

    protected function findInExistingLocations(string $name): ?Location
    {
        return Location::where('city', 'LIKE', "%{$name}%")
            ->orWhere('state', 'LIKE', "%{$name}%")
            ->orWhere('country', 'LIKE', "%{$name}%")
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->first();
    }

    protected function findInWaypoints(string $name): ?Waypoint
    {
        return Waypoint::where('name', 'LIKE', "%{$name}%")
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->first();
    }

    protected function findInRoutes(string $name): ?Route
    {
        return Route::where('name', 'LIKE', "%{$name}%")
            ->with('segments.fromWaypoint')
            ->first();
    }

    protected function geocode(string $location): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/search';
        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => 'TravelAI Nepal Safety System',
                'Accept-Language' => 'en',
            ])
            ->get($url, [
                'q' => $location . ', Nepal',
                'format' => 'json',
                'limit' => 1,
            ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();
        if (empty($data)) {
            return null;
        }

        $result = $data[0];
        return [
            'latitude' => (float) $result['lat'],
            'longitude' => (float) $result['lon'],
            'district' => null,
            'province' => null,
            'source' => 'geocoding',
        ];
    }

    protected function fuzzyMatch(string $name): ?array
    {
        $allWaypoints = Waypoint::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $bestMatch = null;
        $bestScore = 0;

        foreach ($allWaypoints as $wp) {
            similar_text(strtolower($name), strtolower($wp->name), $score);
            if ($score > $bestScore && $score > 60) {
                $bestScore = $score;
                $bestMatch = $wp;
            }
        }

        if ($bestMatch) {
            return [
                'latitude' => $bestMatch->latitude,
                'longitude' => $bestMatch->longitude,
                'district' => null,
                'province' => null,
                'source' => 'fuzzy_match',
                'confidence' => $bestScore / 100,
            ];
        }

        return null;
    }

    public function resolveByCoordinates(float $lat, float $lng): ?array
    {
        $waypoints = Waypoint::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($waypoints as $wp) {
            $distance = $this->haversineDistance($lat, $lng, $wp->latitude, $wp->longitude);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $wp;
            }
        }

        if ($nearest && $minDistance < 50000) {
            return [
                'waypoint' => $nearest,
                'distance' => $minDistance,
                'source' => 'nearest_waypoint',
            ];
        }

        return null;
    }

    protected function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}