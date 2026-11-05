<?php

namespace Tests\Feature\Claim;

use App\Models\BookingClaim;
use App\Models\BookingClaimAttempt;
use App\Models\BookingClaimEvent;
use App\Models\RestaurantOrder;
use App\Models\User;
use App\Services\Claim\ReceiptCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RestaurantClaimTest extends TestCase
{
    use RefreshDatabase;

    protected User $traveler;
    protected User $otherTraveler;
    protected User $admin;
    protected User $provider;
    protected int $providerId;
    protected int $serviceId;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'claim.restaurant_enabled' => true,
            'claim.code_secret' => 'test-secret-key-32-characters-long',
            'claim.code_secret_old' => null,
            'claim.rate_limit.ip_per_15min' => 5,
            'claim.rate_limit.order_per_day' => 3,
            'claim.rate_limit.user_per_day' => 10,
        ]);

        RateLimiter::clear('claim:ip:127.0.0.1');

        $this->traveler = User::factory()->create(['role' => 'traveler']);
        $this->otherTraveler = User::factory()->create(['role' => 'traveler']);
        $this->admin = User::factory()->create(['role' => 'super_admin']);
        $this->provider = User::factory()->create(['role' => 'provider_owner']);

        // Create provider record
        $this->providerId = DB::table('providers')->insertGetId([
            'user_id' => $this->provider->id,
            'name' => 'Test Restaurant',
            'code' => 'P' . substr(strtoupper(uniqid()), 0, 3),
            'slug' => 'test-restaurant-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create service
        $this->serviceId = DB::table('services')->insertGetId([
            'provider_id' => $this->providerId,
            'service_category_id' => 1,
            'name' => 'Test Service',
            'slug' => 'test-service-' . uniqid(),
            'price' => 100,
            'currency' => 'NPR',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function unauthenticated_redirects_to_login()
    {
        $this->get('/traveler/restaurant/claim')->assertRedirect('/login');
    }

    /** @test */
    public function non_traveler_gets_403()
    {
        $this->actingAs($this->provider)
             ->get('/traveler/restaurant/claim')
             ->assertStatus(403);
    }

    /** @test */
    public function feature_flag_off_returns_404()
    {
        config(['claim.restaurant_enabled' => false]);
        $this->actingAs($this->traveler)
             ->get('/traveler/restaurant/claim')
             ->assertStatus(404);
    }

    /** @test */
    public function form_loads_for_traveler()
    {
        $this->actingAs($this->traveler)
             ->get('/traveler/restaurant/claim')
             ->assertStatus(200)
             ->assertViewIs('traveler.restaurant.claim-form');
    }

    /** @test */
    public function invalid_order_number_returns_generic_error()
    {
        $response = $this->actingAs($this->traveler)
             ->post('/traveler/restaurant/claim/initiate', [
                 'order_number' => 'NON-EXISTENT-999',
                 'method' => 'receipt',
                 'receipt_code' => 'ABCD1234',
             ]);

        $response->assertSessionHasErrors('error');
        $response->assertSessionHasErrors(['error' => __('messages.claim_err_generic')]);
    }

    /** @test */
    public function wrong_email_returns_generic_error()
    {
        $order = $this->createOrder(['customer_email' => 'real@example.com']);

        $response = $this->actingAs($this->traveler)
             ->post('/traveler/restaurant/claim/initiate', [
                 'order_number' => $order->order_number,
                 'method' => 'email',
                 'email' => 'wrong@example.com',
             ]);

        $response->assertSessionHasErrors('error');
        $response->assertSessionHasErrors(['error' => __('messages.claim_err_generic')]);
    }

    /** @test */
    public function correct_email_creates_attempt_and_sends_otp()
    {
        \Mail::fake();
        $order = $this->createOrder(['customer_email' => 'real@example.com']);

        $response = $this->actingAs($this->traveler)
             ->post('/traveler/restaurant/claim/initiate', [
                 'order_number' => $order->order_number,
                 'method' => 'email',
                 'email' => 'real@example.com',
             ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('booking_claim_attempts', [
            'claimable_type' => RestaurantOrder::class,
            'claimable_id' => $order->id,
            'traveler_id' => $this->traveler->id,
            'contact_method' => 'email',
            'status' => 'pending',
        ]);

        $attempt = BookingClaimAttempt::where('claimable_id', $order->id)->first();
        $this->assertNotNull($attempt->otp_hash);

        // Hash must be 64-char hex (HMAC-SHA256)
        $this->assertSame(64, strlen($attempt->otp_hash));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $attempt->otp_hash);
        $this->assertNull($attempt->otp_plain ?? null); // no plaintext column

        // Audit event
        $this->assertDatabaseHas('booking_claim_events', [
            'event_type' => 'otp_sent',
            'claimable_id' => $order->id,
        ]);
    }

    /** @test */
    public function wrong_otp_increments_attempts_and_returns_error()
    {
        $order = $this->createOrder(['customer_email' => 'real@example.com']);
        $attempt = $this->createAttempt($order, $this->traveler, '123456');

        $response = $this->actingAs($this->traveler)
             ->post("/traveler/restaurant/claim/verify/{$attempt->id}", [
                 'otp' => '000000', // wrong
             ]);

        $response->assertSessionHasErrors('error');
        $attempt->refresh();
        $this->assertEquals(1, $attempt->otp_attempts);

        $this->assertDatabaseHas('booking_claim_events', [
            'event_type' => 'otp_wrong',
            'claimable_id' => $order->id,
        ]);
    }

    /** @test */
    public function correct_otp_creates_claim_and_invalidates_receipt()
    {
        $order = $this->createOrder(['customer_email' => 'real@example.com']);
        // Set receipt code so we can verify invalidation
        $svc = app(ReceiptCodeService::class);
        $order->update([
            'receipt_code_hash' => $svc->hash('TESTCODE'),
            'receipt_code_issued_at' => now(),
        ]);

        $attempt = $this->createAttempt($order, $this->traveler, '123456');

        $response = $this->actingAs($this->traveler)
             ->post("/traveler/restaurant/claim/verify/{$attempt->id}", [
                 'otp' => '123456',
             ]);

        $response->assertRedirect(route('traveler.dashboard-v2'));

        $this->assertDatabaseHas('booking_claims', [
            'claimable_type' => RestaurantOrder::class,
            'claimable_id' => $order->id,
            'traveler_id' => $this->traveler->id,
        ]);

        $order->refresh();
        $this->assertNotNull($order->receipt_code_used_at);
        $this->assertNull($order->receipt_code_hash);

        $this->assertDatabaseHas('booking_claim_events', [
            'event_type' => 'claimed',
            'claimable_id' => $order->id,
        ]);
    }

    /** @test */
    public function correct_receipt_code_creates_claim()
    {
        $order = $this->createOrder();
        $svc = app(ReceiptCodeService::class);
        $plainCode = 'ABCD1234';
        $order->update([
            'receipt_code_hash' => $svc->hash($plainCode),
            'receipt_code_issued_at' => now(),
        ]);

        $response = $this->actingAs($this->traveler)
             ->post('/traveler/restaurant/claim/initiate', [
                 'order_number' => $order->order_number,
                 'method' => 'receipt',
                 'receipt_code' => $plainCode,
             ]);

        $response->assertRedirect(route('traveler.dashboard-v2'));

        $this->assertDatabaseHas('booking_claims', [
            'claimable_id' => $order->id,
            'traveler_id' => $this->traveler->id,
        ]);
    }

    /** @test */
    public function wrong_receipt_code_returns_generic_error()
    {
        $order = $this->createOrder();
        $svc = app(ReceiptCodeService::class);
        $order->update([
            'receipt_code_hash' => $svc->hash('GOODCODE'),
            'receipt_code_issued_at' => now(),
        ]);

        $response = $this->actingAs($this->traveler)
             ->post('/traveler/restaurant/claim/initiate', [
                 'order_number' => $order->order_number,
                 'method' => 'receipt',
                 'receipt_code' => 'BADCODE1',
             ]);

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('booking_claim_events', [
            'event_type' => 'receipt_wrong',
            'claimable_id' => $order->id,
        ]);
    }

    /** @test */
    public function same_user_can_reclaim_idempotently()
    {
        $order = $this->createOrder();
        // Already claimed by this traveler
        BookingClaim::create([
            'claimable_type' => RestaurantOrder::class,
            'claimable_id' => $order->id,
            'traveler_id' => $this->traveler->id,
            'claimed_at' => now(),
        ]);

        $response = $this->actingAs($this->traveler)
             ->post('/traveler/restaurant/claim/initiate', [
                 'order_number' => $order->order_number,
                 'method' => 'receipt',
                 'receipt_code' => 'ANYTHING',
             ]);

        $response->assertRedirect(route('traveler.dashboard-v2'));
        $response->assertSessionHas('info');

        // Only 1 claim exists
        $this->assertEquals(1, BookingClaim::where('claimable_id', $order->id)->count());
    }

    /** @test */
    public function different_user_cannot_claim_already_claimed_order()
    {
        $order = $this->createOrder();
        // Claimed by OTHER traveler
        BookingClaim::create([
            'claimable_type' => RestaurantOrder::class,
            'claimable_id' => $order->id,
            'traveler_id' => $this->otherTraveler->id,
            'claimed_at' => now(),
        ]);

        $response = $this->actingAs($this->traveler)
             ->post('/traveler/restaurant/claim/initiate', [
                 'order_number' => $order->order_number,
                 'method' => 'receipt',
                 'receipt_code' => 'ANYTHING',
             ]);

        $response->assertSessionHasErrors('error');
        $response->assertSessionHasErrors(['error' => __('messages.claim_err_generic')]);

        // Still only 1 claim (by other traveler)
        $this->assertEquals(1, BookingClaim::where('claimable_id', $order->id)->count());
    }

    /** @test */
    public function expired_otp_returns_error()
    {
        $order = $this->createOrder(['customer_email' => 'real@example.com']);
        $attempt = $this->createAttempt($order, $this->traveler, '123456', -10); // expired 10 min ago

        $response = $this->actingAs($this->traveler)
             ->post("/traveler/restaurant/claim/verify/{$attempt->id}", [
                 'otp' => '123456',
             ]);

        $response->assertSessionHasErrors('error');
        $attempt->refresh();
        $this->assertEquals('expired', $attempt->status);
    }

    /** @test */
    public function max_otp_attempts_locks_claim()
    {
        $order = $this->createOrder(['customer_email' => 'real@example.com']);
        $attempt = $this->createAttempt($order, $this->traveler, '123456');
        $attempt->update(['otp_attempts' => 4]); // Already 4 attempts

        // 5th wrong attempt → locks
        $this->actingAs($this->traveler)
             ->post("/traveler/restaurant/claim/verify/{$attempt->id}", [
                 'otp' => '000000',
             ]);

        $attempt->refresh();
        $this->assertEquals('locked', $attempt->status);
    }

    /** @test */
    public function used_receipt_code_rejected()
    {
        $order = $this->createOrder();
        $svc = app(ReceiptCodeService::class);
        $order->update([
            'receipt_code_hash' => $svc->hash('USED1234'),
            'receipt_code_issued_at' => now()->subDays(5),
            'receipt_code_used_at' => now()->subDays(1), // Already used
        ]);

        $response = $this->actingAs($this->traveler)
             ->post('/traveler/restaurant/claim/initiate', [
                 'order_number' => $order->order_number,
                 'method' => 'receipt',
                 'receipt_code' => 'USED1234',
             ]);

        $response->assertSessionHasErrors('error');
    }

    /** @test */
    public function audit_metadata_never_contains_otp_or_code()
    {
        $order = $this->createOrder(['customer_email' => 'real@example.com']);
        $attempt = $this->createAttempt($order, $this->traveler, '123456');

        $this->actingAs($this->traveler)
             ->post("/traveler/restaurant/claim/verify/{$attempt->id}", [
                 'otp' => '123456',
             ]);

        foreach (BookingClaimEvent::all() as $event) {
            if ($event->metadata) {
                $json = json_encode($event->metadata);
                $this->assertStringNotContainsString('123456', $json);
                $this->assertStringNotContainsString('otp_hash', $json);
                $this->assertStringNotContainsString('receipt_code', $json);
            }
        }
    }

    // ═══════════════════════════════════════════════════
    // ADMIN REISSUE TESTS
    // ═══════════════════════════════════════════════════

    /** @test */
    public function admin_can_view_reissue_form()
    {
        $order = $this->createOrder();

        $this->actingAs($this->admin)
             ->get(route('admin.restaurant.orders.reissue.form', $order))
             ->assertStatus(200)
             ->assertViewIs('admin.restaurant.orders.reissue');
    }

    /** @test */
    public function non_admin_cannot_reissue()
    {
        $order = $this->createOrder();

        $this->actingAs($this->traveler)
             ->post(route('admin.restaurant.orders.reissue', $order))
             ->assertStatus(403);
    }

    /** @test */
    public function admin_cannot_reissue_claimed_order()
    {
        $order = $this->createOrder();
        BookingClaim::create([
            'claimable_type' => RestaurantOrder::class,
            'claimable_id' => $order->id,
            'traveler_id' => $this->traveler->id,
            'claimed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
             ->post(route('admin.restaurant.orders.reissue', $order));

        $response->assertSessionHas('error');
        $response->assertSessionHas('error', __('messages.admin_restaurant_reissue_claimed'));
    }

    /** @test */
    public function admin_reissue_increments_count_and_resets_used_at()
    {
        $order = $this->createOrder();
        $svc = app(ReceiptCodeService::class);
        $order->update([
            'receipt_code_hash' => $svc->hash('OLDCODE1'),
            'receipt_code_issued_at' => now()->subDays(10),
            'receipt_code_used_at' => now()->subDays(5), // Previously used (should reset)
            'receipt_code_reissue_count' => 1,
        ]);

        $this->actingAs($this->admin)
             ->post(route('admin.restaurant.orders.reissue', $order));

        $order->refresh();
        $this->assertEquals(2, $order->receipt_code_reissue_count);
        $this->assertNull($order->receipt_code_used_at); // Reset
        $this->assertNotNull($order->receipt_code_hash); // New hash
        $this->assertNotNull($order->receipt_code_issued_at);
    }

    /** @test */
    public function admin_reissue_max_limit_reached()
    {
        $order = $this->createOrder();
        $order->update([
            'receipt_code_hash' => 'some-hash',
            'receipt_code_issued_at' => now(),
            'receipt_code_reissue_count' => 3, // Max reached
        ]);

        // No recent reissues in events (so max is 3)
        $response = $this->actingAs($this->admin)
             ->post(route('admin.restaurant.orders.reissue', $order));

        $response->assertSessionHas('error');
        $response->assertSessionHas('error', __('messages.admin_restaurant_reissue_max'));
    }

    // ═══════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════

    private function createOrder(array $overrides = []): RestaurantOrder
    {
        $tableId = DB::table('restaurant_tables')->insertGetId([
            'provider_id' => $this->providerId,
            'service_id' => $this->serviceId,
            'table_number' => 'T' . uniqid(),
            'seats' => 4,
            'status' => 'available',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $data = array_merge([
            'order_number' => 'ORD-TEST-' . uniqid(),
            'provider_id' => $this->providerId,
            'service_id' => $this->serviceId,
            'table_id' => $tableId,
            'status' => 'confirmed',
            'subtotal' => 1000,
            'tax' => 0,
            'total' => 1000,
            'currency' => 'NPR',
            'payment_status' => 'paid',
            'customer_name' => 'Test Customer',
            'customer_phone' => '+977-9800000000',
            'customer_email' => null,
            'receipt_code_reissue_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);

        $id = DB::table('restaurant_orders')->insertGetId($data);
        return RestaurantOrder::find($id);
    }

    private function createAttempt(RestaurantOrder $order, User $traveler, string $otp, int $expiryOffsetMinutes = 5): BookingClaimAttempt
    {
        $otpSvc = app(\App\Services\Claim\OtpService::class);

        return BookingClaimAttempt::create([
            'claimable_type' => RestaurantOrder::class,
            'claimable_id' => $order->id,
            'traveler_id' => $traveler->id,
            'contact_method' => 'email',
            'contact_value' => $order->customer_email ?? 'test@example.com',
            'otp_hash' => $otpSvc->hash($otp),
            'otp_expires_at' => now()->addMinutes($expiryOffsetMinutes),
            'otp_attempts' => 0,
            'otp_sent_at' => now(),
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
        ]);
    }
}