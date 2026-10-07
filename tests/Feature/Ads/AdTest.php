<?php

namespace Tests\Feature\Ads;

use App\Models\Ad;
use App\Services\AdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Create a minimal provider via direct DB (no factory needed).
     */
    protected function createProvider(): int
    {
        $userId = DB::table('users')->insertGetId([
            'name'       => 'Test Provider',
            'email'      => 'provider' . uniqid() . '@example.com',
            'password'   => bcrypt('password'),
            'role'       => 'provider_owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('providers')->insertGetId([
            'user_id'             => $userId,
            'name'                => 'Test Provider Co',
            'code'                => 'T' . strtoupper(substr(md5(uniqid()), 0, 2)),
            'slug'                => 'test-provider-' . uniqid(),
            'contact_email'       => 'provider@example.com',
            'verification_status' => 'verified',
            'is_active'           => true,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
    }

    protected function createAd(int $providerId, array $overrides = []): Ad
    {
        return Ad::create(array_merge([
            'provider_id'    => $providerId,
            'title'          => 'Test Ad',
            'image_path'     => 'ads/test.jpg',
            'link_url'       => '/explore',
            'duration_days'  => 15,
            'price_paid'     => 500,
            'status'         => 'active',
            'placement'      => 'dashboard_featured',
            'start_date'     => now()->subDay(),
            'end_date'       => now()->addDays(15),
            'impressions'    => 0,
            'clicks'         => 0,
        ], $overrides));
    }

    // ─────────────────────────────────────────────────────────
    // Test 1-4: Price calculation
    // ─────────────────────────────────────────────────────────

    public function test_price_calculation_7_days(): void
    {
        $this->assertSame(300, Ad::calculatePrice(7));
    }

    public function test_price_calculation_15_days(): void
    {
        $this->assertSame(500, Ad::calculatePrice(15));
    }

    public function test_price_calculation_30_days(): void
    {
        $this->assertSame(800, Ad::calculatePrice(30));
    }

    public function test_price_calculation_invalid_returns_zero(): void
    {
        $this->assertSame(0, Ad::calculatePrice(45));
        $this->assertSame(0, Ad::calculatePrice(0));
    }

    // ─────────────────────────────────────────────────────────
    // Test 5-6: CTR calculation
    // ─────────────────────────────────────────────────────────

    public function test_ctr_calculation(): void
    {
        $providerId = $this->createProvider();
        $ad = $this->createAd($providerId, ['impressions' => 100, 'clicks' => 12]);

        $this->assertSame(12.0, $ad->ctr);
    }

    public function test_ctr_zero_when_no_impressions(): void
    {
        $providerId = $this->createProvider();
        $ad = $this->createAd($providerId, ['impressions' => 0, 'clicks' => 0]);

        $this->assertSame(0.0, $ad->ctr);
    }

    // ─────────────────────────────────────────────────────────
    // Test 7-9: Active scope
    // ─────────────────────────────────────────────────────────

    public function test_active_scope_includes_valid_ads(): void
    {
        $providerId = $this->createProvider();
        $this->createAd($providerId);

        $this->assertSame(1, Ad::active()->count());
    }

    public function test_active_scope_excludes_pending(): void
    {
        $providerId = $this->createProvider();
        $this->createAd($providerId, ['status' => 'pending_review']);

        $this->assertSame(0, Ad::active()->count());
    }

    public function test_active_scope_excludes_expired(): void
    {
        $providerId = $this->createProvider();
        $this->createAd($providerId, ['end_date' => now()->subDay()]);

        $this->assertSame(0, Ad::active()->count());
    }

    // ─────────────────────────────────────────────────────────
    // Test 10: isActiveNow helper
    // ─────────────────────────────────────────────────────────

    public function test_is_active_now_helper(): void
    {
        $providerId = $this->createProvider();

        $activeAd = $this->createAd($providerId);
        $this->assertTrue($activeAd->isActiveNow());

        $expiredAd = $this->createAd($providerId, ['end_date' => now()->subDay()]);
        $this->assertFalse($expiredAd->isActiveNow());

        $pendingAd = $this->createAd($providerId, ['status' => 'pending_review']);
        $this->assertFalse($pendingAd->isActiveNow());
    }

    // ─────────────────────────────────────────────────────────
    // Test 11: Impression tracking
    // ─────────────────────────────────────────────────────────

    public function test_impression_increments_counter(): void
    {
        $providerId = $this->createProvider();
        $ad = $this->createAd($providerId, ['impressions' => 0]);

        $service = app(AdService::class);
        $service->trackImpression($ad, null, '127.0.0.1');

        $ad->refresh();
        $this->assertSame(1, $ad->impressions);
        $this->assertDatabaseCount('ad_impressions', 1);
    }

    // ─────────────────────────────────────────────────────────
    // Test 12: Click tracking
    // ─────────────────────────────────────────────────────────

    public function test_click_increments_counter(): void
    {
        $providerId = $this->createProvider();
        $ad = $this->createAd($providerId, ['clicks' => 0]);

        $service = app(AdService::class);
        $service->trackClick($ad, null, '127.0.0.1');

        $ad->refresh();
        $this->assertSame(1, $ad->clicks);
        $this->assertDatabaseCount('ad_clicks', 1);
    }

    // ─────────────────────────────────────────────────────────
    // Bonus Test: AdService getFeaturedAds
    // ─────────────────────────────────────────────────────────

    public function test_featured_ads_service_returns_active(): void
    {
        $providerId = $this->createProvider();
        $this->createAd($providerId, ['title' => 'Featured Ad']);

        $service = app(AdService::class);
        $service->clearCache();
        $ads = $service->getFeaturedAds();

        $this->assertGreaterThanOrEqual(1, $ads->count());
        $this->assertSame('Featured Ad', $ads->first()->title);
    }
}