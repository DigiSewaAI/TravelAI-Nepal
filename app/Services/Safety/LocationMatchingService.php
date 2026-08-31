<?php

namespace App\Services\Safety;

use App\Models\TravelSafetyIncident;
use App\Models\Waypoint;
use App\Models\Route;
use Illuminate\Support\Facades\Log;

class LocationMatchingService
{
    protected $resolutionService;
    protected $defaultRadius;

    public function __construct(LocationResolutionService $resolutionService)
    {
        $this->resolutionService = $resolutionService;
        $this->defaultRadius = config('safety.matching.default_radius', 5000);
    }

    public function matchIncident(TravelSafetyIncident $incident): array
    {
        $matches = [];

        if ($incident->latitude && $incident->longitude) {
            $matches = $this->matchByCoordinates($incident);
        }

        if ($incident->location_name && empty($matches)) {
            $matches = $this->matchByName($incident);
        }

        if (empty($matches) && ($incident->district || $incident->province)) {
            $matches = $this->matchByAdministrative($incident);
        }

        foreach ($matches as $match) {
            $this->attachMatch($incident, $match);
        }

        return $matches;
    }

    protected function matchByCoordinates(TravelSafetyIncident $incident): array
    {
        $matches = [];
        $radius = $incident->affected_radius ?? $this->defaultRadius;

        // 1. Match Waypoints
        $waypoints = Waypoint::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($wp) use ($incident, $radius) {
                $distance = $this->calculateDistance(
                    $incident->latitude,
                    $incident->longitude,
                    $wp->latitude,
                    $wp->longitude
                );
                if ($distance <= $radius) {
                    return [
                        'type' => 'waypoint',
                        'entity' => $wp,
                        'distance' => $distance,
                        'match_type' => 'coordinate_proximity',
                        'confidence' => max(0, 1 - ($distance / $radius)),
                    ];
                }
                return null;
            })
            ->filter()
            ->values()
            ->toArray();

        $matches = array_merge($matches, $waypoints);

        // 2. Match Routes (via segments)
        $routes = Route::with('segments.fromWaypoint', 'segments.toWaypoint')
            ->get()
            ->filter(function ($route) use ($incident, $radius) {
                foreach ($route->segments as $segment) {
                    $from = $segment->fromWaypoint;
                    $to = $segment->toWaypoint;
                    if ($from && $to) {
                        $dist1 = $this->calculateDistance(
                            $incident->latitude,
                            $incident->longitude,
                            $from->latitude,
                            $from->longitude
                        );
                        $dist2 = $this->calculateDistance(
                            $incident->latitude,
                            $incident->longitude,
                            $to->latitude,
                            $to->longitude
                        );
                        if ($dist1 <= $radius || $dist2 <= $radius) {
                            return true;
                        }
                    }
                }
                return false;
            })
            ->map(function ($route) use ($incident, $radius) {
                $minDistance = PHP_FLOAT_MAX;
                foreach ($route->segments as $segment) {
                    $from = $segment->fromWaypoint;
                    $to = $segment->toWaypoint;
                    if ($from && $to) {
                        $d1 = $this->calculateDistance(
                            $incident->latitude,
                            $incident->longitude,
                            $from->latitude,
                            $from->longitude
                        );
                        $d2 = $this->calculateDistance(
                            $incident->latitude,
                            $incident->longitude,
                            $to->latitude,
                            $to->longitude
                        );
                        $minDistance = min($minDistance, $d1, $d2);
                    }
                }
                return [
                    'type' => 'route',
                    'entity' => $route,
                    'distance' => $minDistance,
                    'match_type' => 'route_proximity',
                    'confidence' => max(0, 1 - ($minDistance / $radius)),
                ];
            })
            ->values()
            ->toArray();

        $matches = array_merge($matches, $routes);

        // Trek matching is completely removed to avoid errors

        return $matches;
    }

    protected function matchByName(TravelSafetyIncident $incident): array
    {
        $matches = [];
        $location = $incident->location_name;

        $resolved = $this->resolutionService->resolve($location);
        if ($resolved && isset($resolved['latitude']) && isset($resolved['longitude'])) {
            $incident->latitude = $resolved['latitude'];
            $incident->longitude = $resolved['longitude'];
            $incident->save();
            return $this->matchByCoordinates($incident);
        }

        $waypoints = Waypoint::where('name', 'LIKE', "%{$location}%")->get();
        foreach ($waypoints as $wp) {
            $matches[] = [
                'type' => 'waypoint',
                'entity' => $wp,
                'distance' => 0,
                'match_type' => 'name_match',
                'confidence' => 0.7,
            ];
        }

        $routes = Route::where('name', 'LIKE', "%{$location}%")->get();
        foreach ($routes as $route) {
            $matches[] = [
                'type' => 'route',
                'entity' => $route,
                'distance' => 0,
                'match_type' => 'name_match',
                'confidence' => 0.6,
            ];
        }

        return $matches;
    }

    protected function matchByAdministrative(TravelSafetyIncident $incident): array
    {
        $matches = [];

        if ($incident->district) {
            $waypoints = Waypoint::where('metadata->district', $incident->district)->get();
            foreach ($waypoints as $wp) {
                $matches[] = [
                    'type' => 'waypoint',
                    'entity' => $wp,
                    'distance' => null,
                    'match_type' => 'district_match',
                    'confidence' => 0.5,
                ];
            }
        }

        if ($incident->province) {
            $waypoints = Waypoint::where('metadata->province', $incident->province)->get();
            foreach ($waypoints as $wp) {
                $matches[] = [
                    'type' => 'waypoint',
                    'entity' => $wp,
                    'distance' => null,
                    'match_type' => 'province_match',
                    'confidence' => 0.4,
                ];
            }
        }

        return $matches;
    }

    protected function attachMatch(TravelSafetyIncident $incident, array $match): void
    {
        $entity = $match['entity'];
        $type = $match['type'];

        switch ($type) {
            case 'waypoint':
                $incident->waypoints()->syncWithoutDetaching([
                    $entity->id => [
                        'distance' => $match['distance'] ?? null,
                        'match_type' => $match['match_type'],
                        'confidence' => $match['confidence'] ?? 0.5,
                    ]
                ]);
                break;
            case 'route':
                $incident->routes()->syncWithoutDetaching([
                    $entity->id => [
                        'distance' => $match['distance'] ?? null,
                        'match_type' => $match['match_type'],
                        'confidence' => $match['confidence'] ?? 0.5,
                    ]
                ]);
                break;
            case 'location':
                $incident->locations()->syncWithoutDetaching([
                    $entity->id => [
                        'distance' => $match['distance'] ?? null,
                        'match_type' => $match['match_type'],
                        'confidence' => $match['confidence'] ?? 0.5,
                    ]
                ]);
                break;
            default:
                Log::warning('Unknown match type', ['type' => $type]);
                break;
        }

        Log::info('Matched incident to entity', [
            'incident_id' => $incident->id,
            'type' => $type,
            'entity_id' => $entity->id,
            'match_type' => $match['match_type'],
        ]);
    }

    protected function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
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