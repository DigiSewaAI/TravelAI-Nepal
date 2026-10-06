<?php

namespace Tests\Feature\Forex;

use App\Models\ExchangeRate;
use App\Services\CurrencyService;
use App\Services\ForexService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ForexIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('forex:all_rates_v1');
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'base_code' => 'USD',
                'rates' => [
                    'NPR' => 155.00,
                    'EUR' => 0.92,
                    'GBP' => 0.79,
                    'INR' => 83.25,
                    'AUD' => 1.52,
                ],
            ], 200),
        ]);
    }

    /** Test 1: CurrencyService uses live rate */
    public function test_currency_service_uses_live_rate(): void
    {
        $cs = app(CurrencyService::class);
        $rate = $cs->getLiveUsdRate();

        $this->assertEqualsWithDelta(155.00, $rate, 0.01);
    }

    /** Test 2: CurrencyService falls back when ForexService returns null (API+DB both fail) */
    public function test_currency_service_fallback_when_api_down(): void
    {
        // Mock ForexService to return null (deterministic — no cache/DB race)
        $mockForex = \Mockery::mock(\App\Services\ForexService::class);
        $mockForex->shouldReceive('getRate')
            ->with('USD')
            ->once()
            ->andReturn(null);

        $this->app->instance(\App\Services\ForexService::class, $mockForex);

        // Fresh instance (avoid cached earlier resolution)
        $cs = new \App\Services\CurrencyService();
        $rate = $cs->getLiveUsdRate();

        // Should fall back to config default (152.60)
        $this->assertEqualsWithDelta(152.60, $rate, 0.01);
    }

    /** Test 3: convert uses live rate */
    public function test_convert_uses_live_rate(): void
    {
        $cs = app(CurrencyService::class);
        $npr = $cs->convert(100, 'USD', 'NPR');

        $this->assertEqualsWithDelta(15500.00, $npr, 1.0);
    }

    /** Test 4: getRateForBooking uses snapshot when available */
    public function test_currency_service_uses_booking_snapshot(): void
    {
        $cs = app(CurrencyService::class);

        // Fake booking object with snapshot
        $booking = (object) ['exchange_rate_snapshot' => 145.00];

        $rate = $cs->getRateForBooking($booking);
        $this->assertEqualsWithDelta(145.00, $rate, 0.01);
    }

    /** Test 5: getRateForBooking falls back to live when no snapshot */
    public function test_currency_service_falls_back_to_live_without_snapshot(): void
    {
        $cs = app(CurrencyService::class);

        // Fake booking without snapshot
        $booking = (object) ['exchange_rate_snapshot' => null];

        $rate = $cs->getRateForBooking($booking);
        $this->assertEqualsWithDelta(155.00, $rate, 0.01);
    }

    /** Test 6: forex:update command runs */
    public function test_forex_update_command_runs(): void
    {
        $this->artisan('forex:update')
            ->assertExitCode(0);

        $this->assertGreaterThan(0, ExchangeRate::count());
    }

    /** Test 7: nprToUsd uses live rate */
    public function test_npr_to_usd_uses_live_rate(): void
    {
        $cs = app(CurrencyService::class);
        $usd = $cs->nprToUsd(15500.00);

        $this->assertEqualsWithDelta(100.00, $usd, 1.0);
    }
}