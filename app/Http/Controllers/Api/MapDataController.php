<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Waypoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * GLOBE-01: Public map data endpoint.
 *
 * Read-only. Exposes waypoints, routes, categories, and aggregate
 * service counts for the public interactive map. No PII. No provider
 * data. No mutations.
 *
 * Contract: MASTER-approved, GLOBE-01 Discovery Report, Section B.
 */
class MapDataController extends Controller
{
    /** Cache key — bump v1 to v2 on breaking schema changes. */
    private const CACHE_KEY = 'map:init:v1';

    /** Cache TTL in seconds (10 minutes). */
    private const CACHE_TTL = 600;

    /**
     * GET /api/map/init
     *
     * Returns the full map dataset. Cached for 10 minutes.
     * Rate-limited via `throttle:api` middleware (30 req/min).
     */
    public function init(): JsonResponse
    {
        $wasCached = Cache::has(self::CACHE_KEY);

        $payload = Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn () => $this->buildPayload()
        );

        // cache_hit is computed per request — never cached.
        $payload['data']['meta']['cache_hit'] = $wasCached;

        return response()->json([
            'success' => true,
            'data'    => $payload['data'],
        ]);
    }

    /**
     * Build the full map payload from the database.
     *
     * Query count (cold cache): 5
     *   - 1 waypoints + 1 eager location load
     *   - 1 routes
     *   - 1 categories
     *   - 1 service_counts aggregate
     */
    private function buildPayload(): array
    {
        $waypoints = $this->fetchWaypoints();
        $routes    = $this->fetchRoutes();
        $categories = $this->fetchCategories();
        $serviceCounts = $this->fetchServiceCounts();

        return [
            'data' => [
                'waypoints' => $waypoints,
                'routes'    => $routes,
                'categories' => $categories,
                'service_counts' => $serviceCounts,
                'center' => [
                    'lat' => 28.3949,
                    'lng' => 84.1240,
                ],
                'meta' => [
                    'waypoints_count'  => count($waypoints),
                    'routes_count'     => count($routes),
                    'categories_count' => count($categories),
                    'generated_at'     => now()->toIso8601String(),
                    // cache_hit is injected by init() — not stored here.
                ],
            ],
        ];
    }

    /**
     * Fetch all active (non-soft-deleted) waypoints, with location metadata.
     *
     * @return array<int, array<string, mixed>>
     */
    private function fetchWaypoints(): array
    {
        return Waypoint::query()
            ->select([
                'id', 'name', 'slug', 'type',
                'latitude', 'longitude', 'altitude',
                'is_overnight_stop', 'location_id',
            ])
            ->with(['location:id,state,city'])
            ->orderBy('id')
            ->get()
            ->map(fn (Waypoint $w) => [
                'id'           => $w->id,
                'name'         => $w->name,
                'slug'         => $w->slug,
                'type'         => $w->type,
                'lat'          => (float) $w->latitude,
                'lng'          => (float) $w->longitude,
                'altitude'     => $w->altitude,
                'is_overnight' => (bool) $w->is_overnight_stop,
                'location_id'  => $w->location_id,
                // RAW location metadata — not province/district semantics.
                'state'        => $w->location?->state,
                'city'         => $w->location?->city,
            ])
            ->values()
            ->all();
    }

    /**
     * Fetch all active, non-soft-deleted routes.
     *
     * STOP-01 resolved: 138 rows expected (143 total, 5 soft-deleted).
     *
     * @return array<int, array<string, mixed>>
     */
    private function fetchRoutes(): array
    {
        return Route::query()
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->select([
                'id', 'name', 'slug', 'route_type', 'difficulty',
                'duration_days', 'max_altitude', 'service_category_id',
            ])
            ->orderBy('id')
            ->get()
            ->map(fn (Route $r) => [
                'id'            => $r->id,
                'name'          => $r->name,
                'slug'          => $r->slug,
                'type'          => $r->route_type,
                'difficulty'    => $r->difficulty,
                'duration_days' => $r->duration_days,
                'max_altitude'  => $r->max_altitude,
                'category_id'   => $r->service_category_id,
            ])
            ->values()
            ->all();
    }

    /**
     * Fetch all service categories.
     *
     * @return array<int, array<string, mixed>>
     */
    private function fetchCategories(): array
    {
        return ServiceCategory::query()
            ->select(['id', 'name', 'slug'])
            ->orderBy('id')
            ->get()
            ->map(fn (ServiceCategory $c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ])
            ->values()
            ->all();
    }

    /**
     * Aggregate active services per location.
     *
     * Returns: { "<location_id>": <count>, ... }
     * No service details, no pricing, no PII.
     *
     * @return array<int, int>
     */
    private function fetchServiceCounts(): array
    {
        return Service::query()
            ->where('status', 'active')
            ->whereNotNull('location_id')
            ->select('location_id', DB::raw('COUNT(*) as count'))
            ->groupBy('location_id')
            ->pluck('count', 'location_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    /**
     * GLOBE-06: Public single-route geometry endpoint.
     *
     * Resolves an active route by slug and returns its ordered
     * waypoint-endpoint polyline.
     *
     * - Only active routes (is_active=1, deleted_at NULL)
     * - Only active segments (deleted_at NULL)
     * - Only active waypoints (deleted_at NULL)
     * - Uses route_segments — NEVER routes.segments JSON
     * - Cached 10 min per slug
     */
    public function route(string $slug): JsonResponse
    {
        $cacheKey = "map:route:v1:{$slug}";

        $payload = Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            fn () => $this->buildRoutePayload($slug)
        );

        if ($payload === null) {
            return response()->json([
                'success' => false,
                'error'   => 'route_not_found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $payload,
        ]);
    }

    /**
     * Build ordered geometry for one active route.
     *
     * Algorithm:
     *   1. First segment contributes its FROM waypoint.
     *   2. Every segment contributes its TO waypoint.
     *   3. Result = N+1 ordered coordinates for N segments.
     *
     * @return array<string, mixed>|null  Null when route/geometry invalid.
     */
    private function buildRoutePayload(string $slug): ?array
    {
        $route = Route::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->select([
                'id', 'name', 'slug', 'route_type', 'difficulty',
                'duration_days', 'max_altitude', 'service_category_id',
            ])
            ->first();

        if (!$route) {
            return null;
        }

        $segments = DB::table('route_segments')
            ->where('route_id', $route->id)
            ->whereNull('deleted_at')
            ->orderBy('sequence')
            ->get(['id', 'from_waypoint_id', 'to_waypoint_id', 'sequence']);

        $meta = [
            'id'            => $route->id,
            'name'          => $route->name,
            'slug'          => $route->slug,
            'type'          => $route->route_type,
            'difficulty'    => $route->difficulty,
            'duration_days' => $route->duration_days,
            'max_altitude'  => $route->max_altitude,
            'category_id'   => $route->service_category_id,
        ];

        if ($segments->isEmpty()) {
            return [
                'route'          => $meta,
                'geometry'       => [],
                'segments_count' => 0,
                'meta'           => ['generated_at' => now()->toIso8601String()],
            ];
        }

        $wpIds = $segments
            ->pluck('from_waypoint_id')
            ->merge($segments->pluck('to_waypoint_id'))
            ->unique()
            ->values()
            ->all();

        $waypoints = Waypoint::query()
            ->whereIn('id', $wpIds)
            ->whereNull('deleted_at')
            ->select(['id', 'name', 'latitude', 'longitude'])
            ->get()
            ->keyBy('id');

        $geometry = [];
        $seq = 1;

        foreach ($segments as $i => $seg) {
            if ($i === 0) {
                $from = $waypoints[$seg->from_waypoint_id] ?? null;
                if (!$from || $from->latitude === null || $from->longitude === null) {
                    return null;
                }
                $geometry[] = [
                    'lat'     => (float) $from->latitude,
                    'lng'     => (float) $from->longitude,
                    'seq'     => $seq++,
                    'wp_id'   => $from->id,
                    'wp_name' => $from->name,
                ];
            }

            $to = $waypoints[$seg->to_waypoint_id] ?? null;
            if (!$to || $to->latitude === null || $to->longitude === null) {
                return null;
            }
            $geometry[] = [
                'lat'     => (float) $to->latitude,
                'lng'     => (float) $to->longitude,
                'seq'     => $seq++,
                'wp_id'   => $to->id,
                'wp_name' => $to->name,
            ];
        }

        return [
            'route'          => $meta,
            'geometry'       => $geometry,
            'segments_count' => $segments->count(),
            'meta'           => ['generated_at' => now()->toIso8601String()],
        ];
    }

    /**
     * GLOBE-07: Session-scoped latest journey.
     *
     * Resolves the latest planner result for the current session and
     * returns an ordered itinerary-day geometry + route metadata.
     *
     * - Read-only, session-scoped only (NO result-ID lookup)
     * - Reuses existing route geometry builder for route overlay
     * - 404 when no journey exists for the session
     */
    public function journey(): JsonResponse
    {
        $sessionId = session()->getId();

        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'error'   => 'no_journey_found',
            ], 404);
        }

        // Session-scoped cache key (session id never leaves this method)
        $cacheKey = 'map:journey:v1:' . hash('sha256', $sessionId);

        $payload = Cache::remember(
            $cacheKey,
            300,
            fn () => $this->buildJourneyPayload($sessionId)
        );

        if ($payload === null) {
            return response()->json([
                'success' => false,
                'error'   => 'no_journey_found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $payload,
        ]);
    }

    /**
     * Build the latest journey for a session.
     *
     * @return array<string, mixed>|null
     */
    private function buildJourneyPayload(string $sessionId): ?array
    {
        $request = DB::table('planner_requests')
            ->where('session_id', $sessionId)
            ->orderByDesc('id')
            ->first(['id', 'route_id', 'destination', 'days']);

        if (!$request) {
            return null;
        }

        $result = DB::table('planner_results')
            ->where('request_id', $request->id)
            ->first(['id']);

        if (!$result) {
            return null;
        }

        $days = DB::table('itinerary_days')
            ->where('result_id', $result->id)
            ->orderBy('day_number')
            ->get(['day_number', 'title', 'overnight_waypoint_id', 'distance_km', 'altitude_m']);

        if ($days->isEmpty()) {
            return null;
        }

        // Resolve waypoints
        $wpIds = $days->pluck('overnight_waypoint_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $waypoints = Waypoint::query()
            ->whereIn('id', $wpIds)
            ->whereNull('deleted_at')
            ->select(['id', 'name', 'latitude', 'longitude', 'altitude'])
            ->get()
            ->keyBy('id');

        // Build journey markers (overnight stops)
        $geometry = [];
        $gapDays  = 0;

        foreach ($days as $day) {
            $wpId = $day->overnight_waypoint_id;

            if (!$wpId || !isset($waypoints[$wpId])) {
                $gapDays++;
                continue;
            }

            $wp = $waypoints[$wpId];
            if ($wp->latitude === null || $wp->longitude === null) {
                $gapDays++;
                continue;
            }

            $geometry[] = [
                'day_number' => $day->day_number,
                'lat'        => (float) $wp->latitude,
                'lng'        => (float) $wp->longitude,
                'altitude'   => $wp->altitude,
                'wp_id'      => $wp->id,
                'wp_name'    => $wp->name,
            ];
        }

        // Route overlay (reuse existing GLOBE-06 builder, active routes only)
        $routeMeta     = null;
        $routeGeometry = null;

        if ($request->route_id) {
            $routeRow = DB::table('routes')
                ->where('id', $request->route_id)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first(['slug']);

            if ($routeRow) {
                $routePayload = $this->buildRoutePayload($routeRow->slug);
                if ($routePayload) {
                    $routeMeta     = $routePayload['route'];
                    $routeGeometry = $routePayload['geometry'];
                }
            }
        }

        return [
            'journey' => [
                'destination'   => $request->destination,
                'days_count'    => $days->count(),
                'verified_days' => count($geometry),
                'gap_days'      => $gapDays,
            ],
            'route'          => $routeMeta,
            'route_geometry' => $routeGeometry,
            'geometry'       => $geometry,
            'meta'           => [
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }
}