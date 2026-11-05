<?php

namespace Tests\Feature\Claim;

use App\Models\BookingClaimEvent;
use App\Models\User;
use App\Services\Claim\RateLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Tests\TestCase;

class RateLimitServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('claim:ip:1.2.3.4');
        RateLimiter::clear('claim:ip:9.9.9.9');
        RateLimiter::clear('claim:ip:8.8.8.8');
        config([
            'claim.rate_limit.ip_per_15min' => 5,
            'claim.rate_limit.order_per_day' => 3,
            'claim.rate_limit.user_per_day' => 10,
        ]);
    }

    public function test_ip_limit_allows_first_five_attempts(): void
    {
        $user = User::factory()->create(['role' => 'traveler']);
        $service = new RateLimitService();

        for ($i = 0; $i < 5; $i++) {
            $service->assertCanAttempt($user->id, 'App\Models\RestaurantOrder', 1, '1.2.3.4');
        }
        $this->assertTrue(true);
    }

    public function test_ip_limit_blocks_sixth_attempt(): void
    {
        $user = User::factory()->create(['role' => 'traveler']);
        $service = new RateLimitService();

        for ($i = 0; $i < 5; $i++) {
            $service->assertCanAttempt($user->id, 'App\Models\RestaurantOrder', 1, '1.2.3.4');
        }

        $this->expectException(TooManyRequestsHttpException::class);
        $service->assertCanAttempt($user->id, 'App\Models\RestaurantOrder', 1, '1.2.3.4');
    }

    public function test_order_limit_blocks_at_fourth_failure(): void
    {
        $user = User::factory()->create(['role' => 'traveler']);
        $type = 'App\Models\RestaurantOrder';
        $orderId = 42;

        for ($i = 0; $i < 3; $i++) {
            BookingClaimEvent::create([
                'traveler_id' => $user->id,
                'event_type' => 'otp_wrong',
                'claimable_type' => $type,
                'claimable_id' => $orderId,
                'created_at' => now()->subHours(2),
            ]);
        }

        $service = new RateLimitService();
        $this->expectException(TooManyRequestsHttpException::class);
        $service->assertCanAttempt($user->id, $type, $orderId, '9.9.9.9');
    }

    public function test_user_limit_blocks_at_eleventh_failure(): void
    {
        $user = User::factory()->create(['role' => 'traveler']);

        for ($i = 0; $i < 10; $i++) {
            BookingClaimEvent::create([
                'traveler_id' => $user->id,
                'event_type' => 'otp_wrong',
                'claimable_type' => 'App\Models\RestaurantOrder',
                'claimable_id' => 100 + $i,
                'created_at' => now()->subHours(2),
            ]);
        }

        $service = new RateLimitService();
        $this->expectException(TooManyRequestsHttpException::class);
        $service->assertCanAttempt($user->id, 'App\Models\RestaurantOrder', 999, '8.8.8.8');
    }

    public function test_old_failures_do_not_count(): void
    {
        $user = User::factory()->create(['role' => 'traveler']);

        for ($i = 0; $i < 5; $i++) {
            BookingClaimEvent::create([
                'traveler_id' => $user->id,
                'event_type' => 'otp_wrong',
                'claimable_type' => 'App\Models\RestaurantOrder',
                'claimable_id' => 42,
                'created_at' => now()->subDays(2),
            ]);
        }

        $service = new RateLimitService();
        $service->assertCanAttempt($user->id, 'App\Models\RestaurantOrder', 42, '9.9.9.9');
        $this->assertTrue(true);
    }
}