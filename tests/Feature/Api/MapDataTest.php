<?php

namespace Tests\Feature\Api;

use App\Models\Location;
use App\Models\Provider;
use App\Models\Route;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Waypoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * GLOBE-01: MapDataController tests.
 *
 * Self-contained: each test seeds its own minimal data via
 * RefreshDatabase. Does NOT depend on any pre-existing DB state.
 *
 * GLOBE-01 MASTER directive: no hardcoded production counts.
 * All expected counts derived from the seeded test dataset.
 */
class MapDataTest extends TestCase
{
    use RefreshDatabase;

    private const CACHE_KEY = 'map:init:v1';

    /** IDs held for soft-delete exclusion tests. */
    private int $deletedWaypointId;
    private int $deletedRouteId;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget(self::CACHE_KEY);
        $this->seedMapData();
    }

    /**
     * Seed a minimal, deterministic dataset:
     *   - 2 locations
     *   - 2 active waypoints + 1 soft-deleted waypoint
     *   - 1 service category
     *   - 1 active route + 1 soft-deleted route
     *   - 1 active service linked to location #1
     */
    private function seedMapData(): void
    {
        // --- Provider chain (required by services.provider_id FK) ---
        $owner = User::create([
            'name'     => 'Test Owner',
            'email'    => 'test-owner@example.test',
            'password' => 'password123',
            'role'     => 'provider_owner',
        ]);

        $provider = Provider::create([
            'user_id'             => $owner->id,
            'name'                => 'Test Provider',
            'slug'                => 'test-provider',
            'verification_status' => 'verified',
            'is_active'           => true,
        ]);

        // --- Locations ---
        $pokhara = Location::create([
            'country'      => 'Nepal',
            'state'        => 'Gandaki',
            'city'         => 'Pokhara',
            'latitude'     => 28.2096,
            'longitude'    => 83.9857,
            'is_habitable' => true,
        ]);

        $kathmandu = Location::create([
            'country'      => 'Nepal',
            'state'        => 'Bagmati',
            'city'         => 'Kathmandu',
            'latitude'     => 27.7172,
            'longitude'    => 85.3240,
            'is_habitable' => true,
        ]);

        // --- Active waypoints ---
        Waypoint::create([
            'name'              => 'Pokhara',
            'slug'              => 'pokhara-city',
            'type'              => 'city',
            'latitude'          => 28.2096,
            'longitude'         => 83.9857,
            'altitude'          => 827,
            'is_overnight_stop' => true,
            'location_id'       => $pokhara->id,
        ]);

        Waypoint::create([
            'name'              => 'Kathmandu',
            'slug'              => 'kathmandu-city',
            'type'              => 'city',
            'latitude'          => 27.7172,
            'longitude'         => 85.3240,
            'altitude'          => 1400,
            'is_overnight_stop' => true,
            'location_id'       => $kathmandu->id,
        ]);

        // --- Soft-deleted waypoint (must NOT appear in API) ---
        $deletedWp = Waypoint::create([
            'name'              => 'Deleted Place',
            'slug'              => 'deleted-place',
            'type'              => 'village',
            'latitude'          => 28.0,
            'longitude'         => 84.0,
            'altitude'          => 1000,
            'is_overnight_stop' => false,
            'location_id'       => $pokhara->id,
        ]);
        $deletedWp->delete();
        $this->deletedWaypointId = $deletedWp->id;

        // --- Service category ---
        $category = ServiceCategory::create([
            'name'        => 'Trek',
            'slug'        => 'trek',
            'description' => 'Trekking services',
        ]);

        // --- Active route ---
        Route::create([
            'name'                => 'Test Trek',
            'slug'                => 'test-trek',
            'route_type'          => 'trek',
            'difficulty'          => 'moderate',
            'duration_days'       => 7,
            'max_altitude'        => 3000,
            'service_category_id' => $category->id,
            'is_active'           => true,
        ]);

        // --- Soft-deleted route (must NOT appear in API) ---
        $deletedRoute = Route::create([
            'name'          => 'Deleted Trek',
            'slug'          => 'deleted-trek',
            'route_type'    => 'trek',
            'difficulty'    => 'easy',
            'duration_days' => 3,
            'is_active'     => true,
        ]);
        $deletedRoute->delete();
        $this->deletedRouteId = $deletedRoute->id;

        // --- Active service linked to Pokhara ---
        Service::create([
            'provider_id'         => $provider->id,
            'service_category_id' => $category->id,
            'name'                => 'Test Trek Service',
            'slug'                => 'test-trek-service',
            'price'               => 100.00,
            'currency'            => 'USD',
            'status'              => 'active',
            'location_id'         => $pokhara->id,
        ]);
    }

    public function test_endpoint_returns_200(): void
    {
        $this->getJson('/api/map/init')->assertStatus(200);
    }

    public function test_response_has_success_true(): void
    {
        $this->getJson('/api/map/init')->assertJsonPath('success', true);
    }

    public function test_returns_all_active_waypoints(): void
    {
        $expected = Waypoint::whereNull('deleted_at')->count();
        $actual   = count($this->getJson('/api/map/init')->json('data.waypoints'));

        $this->assertEquals($expected, $actual);
        $this->assertGreaterThan(0, $expected, 'Seeded dataset must contain active waypoints');
    }

    public function test_returns_all_active_routes(): void
    {
        $expected = Route::where('is_active', true)
            ->whereNull('deleted_at')
            ->count();
        $actual = count($this->getJson('/api/map/init')->json('data.routes'));

        $this->assertEquals($expected, $actual);
        $this->assertGreaterThan(0, $expected, 'Seeded dataset must contain active routes');
    }

    public function test_returns_all_categories(): void
    {
        $expected = ServiceCategory::count();
        $actual   = count($this->getJson('/api/map/init')->json('data.categories'));

        $this->assertEquals($expected, $actual);
        $this->assertGreaterThan(0, $expected, 'Seeded dataset must contain categories');
    }

    public function test_excludes_soft_deleted_routes(): void
    {
        $returnedIds = collect($this->getJson('/api/map/init')->json('data.routes'))
            ->pluck('id')
            ->all();

        $this->assertNotContains($this->deletedRouteId, $returnedIds);
    }

    public function test_excludes_soft_deleted_waypoints(): void
    {
        $returnedIds = collect($this->getJson('/api/map/init')->json('data.waypoints'))
            ->pluck('id')
            ->all();

        $this->assertNotContains($this->deletedWaypointId, $returnedIds);
    }

    public function test_waypoints_have_required_fields(): void
    {
        $waypoints = $this->getJson('/api/map/init')->json('data.waypoints');
        $this->assertNotEmpty($waypoints);

        $required = ['id', 'name', 'slug', 'type', 'lat', 'lng',
                     'altitude', 'is_overnight', 'location_id', 'state', 'city'];

        foreach ($required as $field) {
            $this->assertArrayHasKey($field, $waypoints[0], "Missing field: $field");
        }
    }

    public function test_routes_have_required_fields(): void
    {
        $routes = $this->getJson('/api/map/init')->json('data.routes');
        $this->assertNotEmpty($routes);

        $required = ['id', 'name', 'slug', 'type', 'difficulty',
                     'duration_days', 'max_altitude', 'category_id'];

        foreach ($required as $field) {
            $this->assertArrayHasKey($field, $routes[0], "Missing field: $field");
        }
    }

    public function test_no_pii_in_response(): void
    {
        $body = $this->getJson('/api/map/init')->getContent();

        $forbidden = [
            '"email"', '"phone"', '"password"',
            '"provider_id"', '"user_id"', '"address"',
            '"contact_email"', '"contact_phone"',
        ];

        foreach ($forbidden as $token) {
            $this->assertStringNotContainsString($token, $body, "Leaked: $token");
        }
    }

    public function test_service_counts_are_aggregate_ints(): void
    {
        $counts = $this->getJson('/api/map/init')->json('data.service_counts');

        $this->assertIsArray($counts);
        $this->assertNotEmpty($counts);

        foreach ($counts as $locId => $count) {
            $this->assertIsInt($count);
            $this->assertGreaterThanOrEqual(1, $count);
        }
    }

    public function test_cache_hit_flips_but_data_stays_identical(): void
    {
        Cache::forget(self::CACHE_KEY);

        $r1 = $this->getJson('/api/map/init');
        $r1->assertJsonPath('data.meta.cache_hit', false);

        $r2 = $this->getJson('/api/map/init');
        $r2->assertJsonPath('data.meta.cache_hit', true);

        $this->assertEquals($r1->json('data.waypoints'),  $r2->json('data.waypoints'));
        $this->assertEquals($r1->json('data.routes'),     $r2->json('data.routes'));
        $this->assertEquals($r1->json('data.categories'), $r2->json('data.categories'));
    }

    public function test_cold_cache_uses_at_most_five_queries(): void
    {
        Cache::forget(self::CACHE_KEY);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->getJson('/api/map/init');

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(5, $count, "Cold cache used $count queries");
    }

    public function test_warm_cache_uses_zero_queries(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->getJson('/api/map/init'); // warm

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->getJson('/api/map/init');

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertEquals(0, $count, "Warm cache used $count queries");
    }

    public function test_post_method_not_allowed(): void
    {
        $this->postJson('/api/map/init')->assertStatus(405);
    }

    public function test_zzz_rate_limit_enforced(): void
    {
        // Isolated IP so this test does not pollute other tests.
        $testIp = '10.99.99.250';

        for ($i = 0; $i < 30; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => $testIp])
                 ->getJson('/api/map/init');
        }

        $this->withServerVariables(['REMOTE_ADDR' => $testIp])
             ->getJson('/api/map/init')
             ->assertStatus(429);
    }
}