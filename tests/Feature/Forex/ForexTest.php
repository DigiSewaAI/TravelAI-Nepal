<?php

namespace Tests\Feature\Forex;

use App\Models\ExchangeRate;
use App\Models\User;
use App\Services\ForexService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ForexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Test 1: Service fetches rates from API
     */
    public function test_service_fetches_rates_from_api(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'base_code' => 'USD',
                'rates' => [
                    'NPR' => 154.19,
                    'EUR' => 0.892,
                    'GBP' => 0.756,
                    'INR' => 96.38,
                    'AUD' => 1.435,
                ],
            ], 200),
        ]);

        $service = app(ForexService::class);
        $rates = $service->getAllRates();

        $this->assertIsArray($rates);
        $this->assertArrayHasKey('USD', $rates);
        $this->assertArrayHasKey('EUR', $rates);
        $this->assertArrayHasKey('GBP', $rates);
        $this->assertArrayHasKey('INR', $rates);
        $this->assertArrayHasKey('AUD', $rates);

        // USD = NPR per USD directly
        $this->assertEqualsWithDelta(154.19, $rates['USD'], 0.01);
    }

    /**
     * Test 2: Service caches rates (1 API call only)
     */
    public function test_service_caches_rates(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'base_code' => 'USD',
                'rates' => ['NPR' => 154.19, 'EUR' => 0.892],
            ], 200),
        ]);

        $service = app(ForexService::class);
        $service->getAllRates();
        $service->getAllRates();
        $service->getAllRates();

        // Only 1 HTTP call should have been made
        Http::assertSentCount(1);
    }

    /**
     * Test 3: Fallback to DB when API fails
     */
    public function test_service_fallback_to_db_when_api_fails(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response('', 500),
        ]);

        ExchangeRate::create([
            'base_currency'   => 'USD',
            'target_currency' => 'NPR',
            'rate'            => 150.00,
            'fetched_at'      => now()->subHours(2),
        ]);

        $service = app(ForexService::class);
        $rates = $service->getAllRates();

        $this->assertArrayHasKey('USD', $rates);
        $this->assertEqualsWithDelta(150.00, $rates['USD'], 0.01);
    }

    /**
     * Test 4: getRate single method
     */
    public function test_service_get_rate_single(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'base_code' => 'USD',
                'rates' => ['NPR' => 154.19],
            ], 200),
        ]);

        $service = app(ForexService::class);
        $rate = $service->getRate('USD');

        $this->assertNotNull($rate);
        $this->assertEqualsWithDelta(154.19, $rate, 0.01);
    }

    /**
     * Test 5: Widget hidden when no rates available
     */
    public function test_widget_hidden_when_no_rates(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response('', 500),
        ]);

        // No DB records either
        $user = User::factory()->create(['role' => 'traveler']);

        $response = $this->actingAs($user)->get(route('traveler.dashboard'));

        $response->assertStatus(200);
        // Widget heading should NOT appear
        $response->assertDontSee('Live currency rates');
    }
}