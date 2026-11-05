<?php

namespace Tests\Feature\Claim;

use App\Models\BookingClaim;
use App\Models\BookingClaimAttempt;
use App\Models\BookingClaimEvent;
use App\Models\TransportBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class TransportClaimTest extends TestCase
{
    use RefreshDatabase;

    protected User $traveler;
    protected User $otherTraveler;
    protected User $provider;
    protected int $providerId;
    protected int $vehicleId;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'claim.transport_enabled' => true,
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
            'name' => 'Test Transport',
            'code' => 'P' . substr(strtoupper(uniqid()), 0, 4),
            'slug' => 'test-transport-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Insert vehicle with all required fields
        $vehicleData = [
            'provider_id' => $this->providerId,
            'vehicle_number' => 'VEH-' . substr(uniqid(), 0, 6),
            'vehicle_type' => 'bus',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $this->vehicleId = DB::table('transport_vehicles')->insertGetId($vehicleData);
    }

    /** @test */
    public function unauthenticated_redirects_to_login()
    {
        $this->get('/traveler/transport/claim')->assertRedirect('/login');
    }

    /** @test */
    public function non_traveler_gets_403()
    {
        $this->actingAs($this->provider)
             ->get('/traveler/transport/claim')->assertStatus(403);
    }

    /** @test */
    public function feature_flag_off_returns_404()
    {
        config(['claim.transport_enabled' => false]);
        $this->actingAs($this->traveler)
             ->get('/traveler/transport/claim')->assertStatus(404);
    }

    /** @test */
    public function form_loads_for_traveler()
    {
        $this->actingAs($this->traveler)
             ->get('/traveler/transport/claim')
             ->assertStatus(200)
             ->assertViewIs('traveler.transport.claim-form');
    }

    /** @test */
    public function invalid_booking_ref_returns_generic_error()
    {
        $this->actingAs($this->traveler)
             ->post('/traveler/transport/claim/initiate', [
                 'booking_ref' => 'NON-EXISTENT',
                 'email' => 'test@example.com',
             ])
             ->assertSessionHasErrors('error');
    }

    /** @test */
    public function wrong_email_returns_generic_error()
    {
        $booking = $this->createBooking(['passenger_email' => 'real@example.com']);
        $this->actingAs($this->traveler)
             ->post('/traveler/transport/claim/initiate', [
                 'booking_ref' => $booking->booking_ref,
                 'email' => 'wrong@example.com',
             ])
             ->assertSessionHasErrors('error');
    }

    /** @test */
    public function correct_email_creates_attempt_and_sends_otp()
    {
        \Mail::fake();
        $booking = $this->createBooking(['passenger_email' => 'real@example.com']);
        $response = $this->actingAs($this->traveler)
             ->post('/traveler/transport/claim/initiate', [
                 'booking_ref' => $booking->booking_ref,
                 'email' => 'real@example.com',
             ]);
        $response->assertRedirect();
        $attempt = BookingClaimAttempt::where('claimable_id', $booking->id)->first();
        $this->assertNotNull($attempt);
        $this->assertEquals('pending', $attempt->status);
        $this->assertSame(64, strlen($attempt->otp_hash));
    }

    /** @test */
    public function wrong_otp_increments_attempts()
    {
        $booking = $this->createBooking(['passenger_email' => 'real@example.com']);
        $attempt = $this->createAttempt($booking, $this->traveler, '123456');
        $this->actingAs($this->traveler)
             ->post("/traveler/transport/claim/verify/{$attempt->id}", ['otp' => '000000']);
        $attempt->refresh();
        $this->assertEquals(1, $attempt->otp_attempts);
    }

    /** @test */
    public function correct_otp_creates_claim()
    {
        $booking = $this->createBooking(['passenger_email' => 'real@example.com']);
        $attempt = $this->createAttempt($booking, $this->traveler, '123456');
        $response = $this->actingAs($this->traveler)
             ->post("/traveler/transport/claim/verify/{$attempt->id}", ['otp' => '123456']);
        $response->assertRedirect(route('traveler.dashboard-v2'));
        $this->assertDatabaseHas('booking_claims', [
            'claimable_type' => TransportBooking::class,
            'claimable_id' => $booking->id,
            'traveler_id' => $this->traveler->id,
        ]);
    }

    /** @test */
    public function same_user_can_reclaim_idempotently()
    {
        $booking = $this->createBooking(['passenger_email' => 'real@example.com']);
        BookingClaim::create([
            'claimable_type' => TransportBooking::class,
            'claimable_id' => $booking->id,
            'traveler_id' => $this->traveler->id,
            'claimed_at' => now(),
        ]);
        $response = $this->actingAs($this->traveler)
             ->post('/traveler/transport/claim/initiate', [
                 'booking_ref' => $booking->booking_ref,
                 'email' => 'real@example.com',
             ]);
        $response->assertRedirect(route('traveler.dashboard-v2'));
        $this->assertEquals(1, BookingClaim::where('claimable_id', $booking->id)->count());
    }

    /** @test */
    public function different_user_cannot_claim_already_claimed()
    {
        $booking = $this->createBooking(['passenger_email' => 'real@example.com']);
        BookingClaim::create([
            'claimable_type' => TransportBooking::class,
            'claimable_id' => $booking->id,
            'traveler_id' => $this->otherTraveler->id,
            'claimed_at' => now(),
        ]);
        $this->actingAs($this->traveler)
             ->post('/traveler/transport/claim/initiate', [
                 'booking_ref' => $booking->booking_ref,
                 'email' => 'real@example.com',
             ])
             ->assertSessionHasErrors('error');
    }

    /** @test */
    public function expired_otp_returns_error()
    {
        $booking = $this->createBooking(['passenger_email' => 'real@example.com']);
        $attempt = $this->createAttempt($booking, $this->traveler, '123456', -10);
        $this->actingAs($this->traveler)
             ->post("/traveler/transport/claim/verify/{$attempt->id}", ['otp' => '123456']);
        $attempt->refresh();
        $this->assertEquals('expired', $attempt->status);
    }

    /** @test */
    public function max_otp_attempts_locks_claim()
    {
        $booking = $this->createBooking(['passenger_email' => 'real@example.com']);
        $attempt = $this->createAttempt($booking, $this->traveler, '123456');
        $attempt->update(['otp_attempts' => 4]);
        $this->actingAs($this->traveler)
             ->post("/traveler/transport/claim/verify/{$attempt->id}", ['otp' => '000000']);
        $attempt->refresh();
        $this->assertEquals('locked', $attempt->status);
    }

    /** @test */
    public function audit_metadata_no_otp()
    {
        $booking = $this->createBooking(['passenger_email' => 'real@example.com']);
        $attempt = $this->createAttempt($booking, $this->traveler, '123456');
        $this->actingAs($this->traveler)
             ->post("/traveler/transport/claim/verify/{$attempt->id}", ['otp' => '123456']);
        foreach (BookingClaimEvent::all() as $e) {
            if ($e->metadata) {
                $json = json_encode($e->metadata);
                $this->assertStringNotContainsString('123456', $json);
                $this->assertStringNotContainsString('otp_hash', $json);
            }
        }
    }

    /** @test */
    public function different_user_data_isolation()
    {
        $b1 = $this->createBooking(['passenger_email' => 'a@example.com']);
        $b2 = $this->createBooking(['passenger_email' => 'b@example.com']);
        BookingClaim::create([
            'claimable_type' => TransportBooking::class,
            'claimable_id' => $b1->id,
            'traveler_id' => $this->traveler->id,
            'claimed_at' => now(),
        ]);
        // traveler2 cannot claim b1
        $this->actingAs($this->otherTraveler)
             ->post('/traveler/transport/claim/initiate', [
                 'booking_ref' => $b1->booking_ref,
                 'email' => 'a@example.com',
             ])
             ->assertSessionHasErrors('error');
    }

    private function createBooking(array $overrides = []): TransportBooking
    {
        $data = array_merge([
            'provider_id' => $this->providerId,
            'vehicle_id' => $this->vehicleId,
            'passenger_name' => 'Test Passenger',
            'passenger_email' => 'test@example.com',
            'booking_ref' => 'TRP-TEST-' . uniqid(),
            'route_name' => 'Kathmandu-Pokhara',
            'travel_date' => now()->addDays(7)->format('Y-m-d'),
            'amount' => 2500,
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);
        $id = DB::table('transport_bookings')->insertGetId($data);
        return TransportBooking::find($id);
    }

    private function createAttempt(TransportBooking $booking, User $traveler, string $otp, int $offset = 5): BookingClaimAttempt
    {
        $otpSvc = app(\App\Services\Claim\OtpService::class);
        return BookingClaimAttempt::create([
            'claimable_type' => TransportBooking::class,
            'claimable_id' => $booking->id,
            'traveler_id' => $traveler->id,
            'contact_method' => 'email',
            'contact_value' => $booking->passenger_email,
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