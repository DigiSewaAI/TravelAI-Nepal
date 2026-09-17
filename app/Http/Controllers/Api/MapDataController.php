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
}