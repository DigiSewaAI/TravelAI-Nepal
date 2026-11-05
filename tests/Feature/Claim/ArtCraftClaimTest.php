<?php

namespace Tests\Feature\Claim;

use App\Models\BookingClaim;
use App\Models\BookingClaimAttempt;
use App\Models\BookingClaimEvent;
use App\Models\CustomOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ArtCraftClaimTest extends TestCase
{
    use RefreshDatabase;

    protected User $traveler;
    protected User $otherTraveler;
    protected User $provider;
    protected int $providerId;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'claim.artcraft_enabled' => true,
            'claim.code_secret' => 'test-secret-key-32-characters-long',
            'claim.rate_limit.ip_per_15min' => 5,
            'claim.rate_limit.order_per_day' => 3,
            'claim.rate_limit.user_per_day' => 10,
        ]);
        RateLimiter::clear('claim:ip:127.0.0.1');
        $this->traveler = User::factory()->create(['role' => 'traveler']);
        $this->otherTraveler = User::factory()->create(['role' => 'traveler']);
        $this->provider = User::factory()->create(['role' => 'provider_owner']);
        $this->providerId = DB::table('providers')->insertGetId([
            'user_id' => $this->provider->id,
            'name' => 'Test ArtCraft',
            'code' => 'A' . substr(strtoupper(uniqid()), 0, 4),
            'slug' => 'test-artcraft-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function unauthenticated_redirects_to_login()
    {
        $this->get('/traveler/artcraft/claim')->assertRedirect('/login');
    }

    /** @test */
    public function non_traveler_gets_403()
    {
        $this->actingAs($this->provider)->get('/traveler/artcraft/claim')->assertStatus(403);
    }

    /** @test */
    public function feature_flag_off_returns_404()
    {
        config(['claim.artcraft_enabled' => false]);
        $this->actingAs($this->traveler)->get('/traveler/artcraft/claim')->assertStatus(404);
    }

    /** @test */
    public function form_loads_for_traveler()
    {
        $this->actingAs($this->traveler)
             ->get('/traveler/artcraft/claim')
             ->assertStatus(200)
             ->assertViewIs('traveler.artcraft.claim-form');
    }

    /** @test */
    public function invalid_order_id_returns_generic_error()
    {
        $this->actingAs($this->traveler)
             ->post('/traveler/artcraft/claim/initiate', [
                 'order_id' => 99999,
                 'email' => 'test@example.com',
             ])
             ->assertSessionHasErrors('error');
    }

    /** @test */
    public function wrong_email_returns_generic_error()
    {
        $order = $this->createOrder(['client_email' => 'real@example.com']);
        $this->actingAs($this->traveler)
             ->post('/traveler/artcraft/claim/initiate', [
                 'order_id' => $order->id,
                 'email' => 'wrong@example.com',
             ])
             ->assertSessionHasErrors('error');
    }

    /** @test */
    public function correct_email_creates_attempt()
    {
        \Mail::fake();
        $order = $this->createOrder(['client_email' => 'real@example.com']);
        $this->actingAs($this->traveler)
             ->post('/traveler/artcraft/claim/initiate', [
                 'order_id' => $order->id,
                 'email' => 'real@example.com',
             ])
             ->assertRedirect();
        $attempt = BookingClaimAttempt::where('claimable_id', $order->id)->first();
        $this->assertNotNull($attempt);
        $this->assertEquals('pending', $attempt->status);
    }

    /** @test */
    public function wrong_otp_increments_attempts()
    {
        $order = $this->createOrder(['client_email' => 'real@example.com']);
        $attempt = $this->createAttempt($order, $this->traveler, '123456');
        $this->actingAs($this->traveler)
             ->post("/traveler/artcraft/claim/verify/{$attempt->id}", ['otp' => '000000']);
        $attempt->refresh();
        $this->assertEquals(1, $attempt->otp_attempts);
    }

    /** @test */
    public function correct_otp_creates_claim()
    {
        $order = $this->createOrder(['client_email' => 'real@example.com']);
        $attempt = $this->createAttempt($order, $this->traveler, '123456');
        $this->actingAs($this->traveler)
             ->post("/traveler/artcraft/claim/verify/{$attempt->id}", ['otp' => '123456'])
             ->assertRedirect(route('traveler.dashboard-v2'));
        $this->assertDatabaseHas('booking_claims', [
            'claimable_type' => CustomOrder::class,
            'claimable_id' => $order->id,
            'traveler_id' => $this->traveler->id,
        ]);
    }

    /** @test */
    public function same_user_can_reclaim_idempotently()
    {
        $order = $this->createOrder(['client_email' => 'real@example.com']);
        BookingClaim::create([
            'claimable_type' => CustomOrder::class,
            'claimable_id' => $order->id,
            'traveler_id' => $this->traveler->id,
            'claimed_at' => now(),
        ]);
        $this->actingAs($this->traveler)
             ->post('/traveler/artcraft/claim/initiate', [
                 'order_id' => $order->id,
                 'email' => 'real@example.com',
             ])
             ->assertRedirect(route('traveler.dashboard-v2'));
        $this->assertEquals(1, BookingClaim::where('claimable_id', $order->id)->count());
    }

    /** @test */
    public function different_user_cannot_claim()
    {
        $order = $this->createOrder(['client_email' => 'real@example.com']);
        BookingClaim::create([
            'claimable_type' => CustomOrder::class,
            'claimable_id' => $order->id,
            'traveler_id' => $this->otherTraveler->id,
            'claimed_at' => now(),
        ]);
        $this->actingAs($this->traveler)
             ->post('/traveler/artcraft/claim/initiate', [
                 'order_id' => $order->id,
                 'email' => 'real@example.com',
             ])
             ->assertSessionHasErrors('error');
    }

    /** @test */
    public function expired_otp_returns_error()
    {
        $order = $this->createOrder(['client_email' => 'real@example.com']);
        $attempt = $this->createAttempt($order, $this->traveler, '123456', -10);
        $this->actingAs($this->traveler)
             ->post("/traveler/artcraft/claim/verify/{$attempt->id}", ['otp' => '123456']);
        $attempt->refresh();
        $this->assertEquals('expired', $attempt->status);
    }

    /** @test */
    public function max_otp_attempts_locks_claim()
    {
        $order = $this->createOrder(['client_email' => 'real@example.com']);
        $attempt = $this->createAttempt($order, $this->traveler, '123456');
        $attempt->update(['otp_attempts' => 4]);
        $this->actingAs($this->traveler)
             ->post("/traveler/artcraft/claim/verify/{$attempt->id}", ['otp' => '000000']);
        $attempt->refresh();
        $this->assertEquals('locked', $attempt->status);
    }

    /** @test */
    public function audit_metadata_no_otp()
    {
        $order = $this->createOrder(['client_email' => 'real@example.com']);
        $attempt = $this->createAttempt($order, $this->traveler, '123456');
        $this->actingAs($this->traveler)
             ->post("/traveler/artcraft/claim/verify/{$attempt->id}", ['otp' => '123456']);
        foreach (BookingClaimEvent::all() as $e) {
            if ($e->metadata) {
                $json = json_encode($e->metadata);
                $this->assertStringNotContainsString('123456', $json);
                $this->assertStringNotContainsString('otp_hash', $json);
            }
        }
    }

    private function createOrder(array $overrides = []): CustomOrder
    {
        $data = array_merge([
            'provider_id' => $this->providerId,
            'client_name' => 'Test Client',
            'client_email' => 'test@example.com',
            'type' => 'Sculpture',
            'budget' => 20000,
            'deadline' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);
        $id = DB::table('custom_orders')->insertGetId($data);
        return CustomOrder::find($id);
    }

    private function createAttempt(CustomOrder $order, User $traveler, string $otp, int $offset = 5): BookingClaimAttempt
    {
        $otpSvc = app(\App\Services\Claim\OtpService::class);
        return BookingClaimAttempt::create([
            'claimable_type' => CustomOrder::class,
            'claimable_id' => $order->id,
            'traveler_id' => $traveler->id,
            'contact_method' => 'email',
            'contact_value' => $order->client_email,
            'otp_hash' => $otpSvc->hash($otp),
            'otp_expires_at' => now()->addMinutes($offset),
            'otp_attempts' => 0,
            'otp_sent_at' => now(),
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
        ]);
    }
}