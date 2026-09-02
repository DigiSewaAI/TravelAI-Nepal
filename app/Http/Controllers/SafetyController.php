<?php

namespace App\Http\Controllers;

use App\Models\TravelSafetyIncident;
use App\Models\Waypoint;
use App\Models\Route;
use App\Models\Trek;
use App\Models\Location;
use App\Services\Safety\SafetyStatusService;
use App\Services\Safety\RiskScoringService;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SafetyController extends Controller
{
    protected $statusService;
    protected $riskService;
    protected $weatherService;

    public function __construct(
        SafetyStatusService $statusService,
        RiskScoringService $riskService,
        WeatherService $weatherService
    ) {
        $this->statusService = $statusService;
        $this->riskService = $riskService;
        $this->weatherService = $weatherService;
    }

    /**
     * Public safety overview page
     */
    public function index()
    {
        $summary = $this->statusService->getDashboardSummary();

        // Get active incidents with location
        $incidents = TravelSafetyIncident::whereIn('status', ['active', 'verified'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('severity', 'desc')
            ->orderBy('reported_at', 'desc')
            ->limit(20)
            ->get();

        // Get affected destinations
        $affectedWaypoints = Waypoint::whereNotNull('safety_status')
            ->where('safety_status', '!=', 'normal')
            ->where('safety_status', '!=', 'unknown')
            ->with('safetyIncidents')
            ->limit(10)
            ->get();

        $affectedTreks = Trek::whereNotNull('safety_status')
            ->where('safety_status', '!=', 'normal')
            ->where('safety_status', '!=', 'unknown')
            ->with('safetyIncidents')
            ->limit(10)
            ->get();

        // ✅ Weather Strip for major trekking nodes
        $weatherStrip = $this->getWeatherStrip();

        return view('safety.index', compact(
            'summary',
            'incidents',
            'affectedWaypoints',
            'affectedTreks',
            'weatherStrip'
        ));
    }

    /**
     * Destination safety detail page
     */
    public function destination(Request $request, string $slug)
    {
        // Try to find as Trek, Waypoint, or Route
        $entity = Trek::where('slug', $slug)->first();
        if (!$entity) {
            $entity = Waypoint::where('slug', $slug)->first();
        }
        if (!$entity) {
            $entity = Route::where('slug', $slug)->first();
        }
        if (!$entity) {
            abort(404, 'Destination not found');
        }

        $status = $this->statusService->getStatusForEntity($entity);
        $incidents = $entity->safetyIncidents()
            ->whereIn('status', ['active', 'verified'])
            ->with('sources')
            ->get();

        // ✅ Weather for this specific destination (if it's a Waypoint or has lat/lng)
        $weather = null;
        if ($entity instanceof Waypoint && $entity->latitude && $entity->longitude) {
            $weather = $this->getWeatherForDestination($entity);
        }

        return view('safety.destination', compact('entity', 'status', 'incidents', 'weather'));
    }

    /**
     * Incident detail page
     */
    public function incident(int $id)
    {
        $incident = TravelSafetyIncident::with(['sources', 'waypoints', 'routes', 'treks'])
            ->findOrFail($id);

        // Get affected entities
        $affectedWaypoints = $incident->waypoints;
        $affectedRoutes = $incident->routes;
        $affectedTreks = $incident->treks;

        // ✅ Weather for the incident's primary location
        $weather = null;
        if ($incident->latitude && $incident->longitude) {
            // Try to find a waypoint near the incident
            $nearbyWaypoint = Waypoint::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->selectRaw(
                    '*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance',
                    [$incident->latitude, $incident->longitude, $incident->latitude]
                )
                ->orderBy('distance')
                ->first();

            if ($nearbyWaypoint) {
                $weather = $this->getWeatherForDestination($nearbyWaypoint);
            }
        }

        return view('safety.incident', compact(
            'incident',
            'affectedWaypoints',
            'affectedRoutes',
            'affectedTreks',
            'weather'
        ));
    }

    /**
     * API endpoint for map markers
     */
    public function markers(Request $request)
    {
        $incidents = TravelSafetyIncident::whereIn('status', ['active', 'verified'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($request->has('severity'), function ($query) use ($request) {
                return $query->where('severity', $request->severity);
            })
            ->get();

        $markers = $incidents->map(function ($incident) {
            return [
                'id' => $incident->id,
                'title' => $incident->title,
                'latitude' => $incident->latitude,
                'longitude' => $incident->longitude,
                'severity' => $incident->severity,
                'status' => $incident->status,
                'type' => $incident->incident_type,
                'location' => $incident->location_name,
                'reported_at' => $incident->reported_at?->toIso8601String(),
                'last_verified' => $incident->last_verified_at?->toIso8601String(),
                'confidence' => $incident->confidence_score,
                'affected_radius' => $incident->affected_radius ?? 5000,
                'color' => $this->getSeverityColor($incident->severity),
                'url' => route('safety.incident', $incident->id),
            ];
        });

        return response()->json($markers);
    }

    /**
     * ✅ NEW: Search destination → Weather + Safety
     */
    public function searchDestination(Request $request)
    {
        $query = $request->get('q');
        
        if (!$query || strlen($query) < 2) {
            return response()->json(['found' => false, 'message' => 'Please enter at least 2 characters']);
        }

        // Search in Waypoints and Routes (prioritize waypoints)
        $results = collect();

        // Search waypoints
        $waypoints = Waypoint::where('name', 'LIKE', "%{$query}%")
            ->orWhere('slug', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($waypoints as $wp) {
            $weather = $this->getWeatherForDestination($wp);
            $safetyStatus = $wp->safety_status ?? 'unknown';
            $incident = $wp->safetyIncidents()
                ->whereIn('status', ['active', 'verified'])
                ->first();

            $results->push([
                'type' => 'waypoint',
                'id' => $wp->id,
                'name' => $wp->name,
                'slug' => $wp->slug,  // ✅ यो सही छ
                'latitude' => $wp->latitude,
                'longitude' => $wp->longitude,
                'altitude' => $wp->altitude,
                'safety_status' => $safetyStatus,
                'weather' => $weather,
                'incident' => $incident ? [
                    'id' => $incident->id,
                    'title' => $incident->title,
                    'severity' => $incident->severity,
                    'slug' => $incident->slug,
                ] : null,
            ]);
        }

        // If no waypoints found, search routes
        if ($results->isEmpty()) {
            $routes = Route::where('name', 'LIKE', "%{$query}%")
                ->orWhere('slug', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($routes as $route) {
                // Try to get a representative waypoint for weather
                $firstWaypoint = $route->waypoints()->first();
                $weather = $firstWaypoint ? $this->getWeatherForDestination($firstWaypoint) : null;
                $safetyStatus = $route->safety_status ?? 'unknown';

                $results->push([
                    'type' => 'route',
                    'id' => $route->id,
                    'name' => $route->name,
                    'slug' => $route->slug,  // ✅ यो सही छ
                    'safety_status' => $safetyStatus,
                    'weather' => $weather,
                    'incident' => null,
                ]);
            }
        }

        return response()->json([
            'found' => $results->isNotEmpty(),
            'results' => $results->take(5)->values(),
        ]);
    }

    /**
     * ✅ NEW: Get weather strip for major trekking nodes
     */
    protected function getWeatherStrip(): array
    {
        $locations = ['Kathmandu', 'Pokhara', 'Lukla', 'Namche Bazaar', 'Manang'];
        $strip = [];

        foreach ($locations as $city) {
            $waypoint = Waypoint::where('name', 'LIKE', "%{$city}%")->first();
            if ($waypoint) {
                $weather = $this->getWeatherForDestination($waypoint);
                if ($weather) {
                    $strip[$city] = [
                        'temp' => $weather['temp'] ?? null,
                        'condition' => $weather['condition'] ?? null,
                        'icon' => $weather['icon'] ?? null,
                    ];
                }
            }
        }

        return $strip;
    }

    /**
     * ✅ NEW: Get weather for a specific destination (cached)
     */
    protected function getWeatherForDestination($entity): ?array
    {
        if (!$entity || !($entity instanceof Waypoint)) {
            return null;
        }

        if (!$entity->latitude || !$entity->longitude) {
            return null;
        }

        $cacheKey = "weather_wp_{$entity->id}";
        
        return Cache::remember($cacheKey, 900, function () use ($entity) {
            try {
                $data = $this->weatherService->getWeatherByCoords(
                    $entity->latitude,
                    $entity->longitude
                );

                if (!$data || isset($data['cod']) && $data['cod'] != 200) {
                    return null;
                }

                return [
                    'temp' => round($data['main']['temp'] ?? 0),
                    'feels_like' => round($data['main']['feels_like'] ?? 0),
                    'humidity' => $data['main']['humidity'] ?? 0,
                    'condition' => $data['weather'][0]['description'] ?? 'Unknown',
                    'icon' => $data['weather'][0]['icon'] ?? '01d',
                    'wind_speed' => $data['wind']['speed'] ?? 0,
                    'precipitation' => $data['rain']['1h'] ?? $data['snow']['1h'] ?? 0,
                ];
            } catch (\Exception $e) {
                \Log::error('Weather fetch failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Helper: Get color for severity
     */
    protected function getSeverityColor(?string $severity): string
    {
        return match ($severity) {
            'critical' => '#dc3545',
            'high' => '#fd7e14',
            'moderate' => '#ffc107',
            'low' => '#28a745',
            default => '#6c757d',
        };
    }
}