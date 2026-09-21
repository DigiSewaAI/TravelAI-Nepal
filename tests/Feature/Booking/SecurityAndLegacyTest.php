<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Departure;
use App\Models\Provider;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceItineraryDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * PROVIDER-ITINERARY-09B-04: Security, legacy, admin transition tests.
 *
 * Covers: T8, T9, T20, T21
 */
class SecurityAndLegacyTest extends TestCase
{
    use RefreshDatabase;

    // T8 — Legacy booking (departure_id NULL) still works
    public function test_T8_legacy_booking_without_departure_still_works(): void
    {
        $owner = User::create([
            'name' => 'Owner', 'email' => 'owner8@example.test',
            'password' => 'password123', 'role' => 'provider_owner',
        ]);
        $provider = Provider::create([
            'user_id' => $owner->id, 'name' => 'P8', 'slug' => 'p8',
            'verification_status' => 'verified', 'is_active' => true,
        ]);
        $category = ServiceCategory::create(['name' => 'Trek', 'slug' => 'trek']);
        $service = Service::create([
            'provider_id' => $provider->id, 'service_category_id' => $category->id,
            'name' => 'Legacy Trek', 'slug' => 'legacy-trek-' . uniqid(),
            'price' => 100, 'currency' => 'USD',
            'status' => 'active',
            'itinerary_status' => 'draft', // no itinerary published
        ]);

        // No departures, no published itinerary — legacy flow
        $this->post(route('public.services.book', $service->slug), [
            'name'       => 'Traveler',
            'email'      => 'traveler8@example.test',
            'phone'      => '9800000000',
            'start_date' => now()->addDays(5)->format('Y-m-d'),
        ])->assertRedirect();

        $booking = Booking::first();
        $this->assertNotNull($booking);
        $this->assertNull($booking->departure_id);
        $this->assertEquals(1, $booking->guest_count);
    }

    // T9 — Foreign departure_id → rejected
    public function test_T9_foreign_departure_rejected(): void
    {
        // Provider A with service A + departure A
        $ownerA = User::create([
            'name' => 'A', 'email' => 'a@example.test',
            'password' => 'p', 'role' => 'provider_owner',
        ]);
        $providerA = Provider::create([
            'user_id' => $ownerA->id, 'name' => 'PA', 'slug' => 'pa',
            'verification_status' => 'verified', 'is_active' => true,
        ]);
        $category = ServiceCategory::create(['name' => 'Trek', 'slug' => 'trek']);
        $serviceA = Service::create([
            'provider_id' => $providerA->id, 'service_category_id' => $category->id,
            'name' => 'SA', 'slug' => 'sa-' . uniqid(),
            'price' => 100, 'currency' => 'USD',
            'status' => 'active', 'itinerary_status' => 'published',
        ]);
        ServiceItineraryDay::create([
            'service_id' => $serviceA->id, 'day_number' => 1, 'title' => 'D1',
        ]);

        // Provider B with service B + departure B
        $ownerB = User::create([
            'name' => 'B', 'email' => 'b@example.test',
            'password' => 'p', 'role' => 'provider_owner',
        ]);
        $providerB = Provider::create([
            'user_id' => $ownerB->id, 'name' => 'PB', 'slug' => 'pb',
            'verification_status' => 'verified', 'is_active' => true,
        ]);
        $serviceB = Service::create([
            'provider_id' => $providerB->id, 'service_category_id' => $category->id,
            'name' => 'SB', 'slug' => 'sb-' . uniqid(),
            'price' => 100, 'currency' => 'USD',
            'status' => 'active', 'itinerary_status' => 'published',
        ]);
        ServiceItineraryDay::create([
            'service_id' => $serviceB->id, 'day_number' => 1, 'title' => 'D1',
        ]);

        $departureB = Departure::create([
            'service_id' => $serviceB->id,
            'start_date' => now()->addDays(10),
            'end_date'   => now()->addDays(17),
            'capacity'   => 10,
            'status'     => 'scheduled',
        ]);

        // Attempt to book service A with departure B (foreign)
        $this->post(route('public.services.book', $serviceA->slug), [
            'name'         => 'T',
            'email'        => 't9@example.test',
            'phone'        => '9800000000',
            'start_date'   => now()->addDays(10)->format('Y-m-d'),
            'departure_id' => $departureB->id,
            'guest_count'  => 1,
        ]);

        $this->assertDatabaseCount('bookings', 0);
    }

    // T20 — Public throttle enforced (10/min)
    public function test_T20_public_booking_throttle_enforced(): void
    {
        $owner = User::create([
            'name' => 'O', 'email' => 'o20@example.test',
            'password' => 'p', 'role' => 'provider_owner',
        ]);
        $provider = Provider::create([
            'user_id' => $owner->id, 'name' => 'P20', 'slug' => 'p20',
            'verification_status' => 'verified', 'is_active' => true,
        ]);
        $category = ServiceCategory::create(['name' => 'Trek', 'slug' => 'trek']);
        $service = Service::create([
            'provider_id' => $provider->id, 'service_category_id' => $category->id,
            'name' => 'S20', 'slug' => 's20-' . uniqid(),
            'price' => 100, 'currency' => 'USD',
            'status' => 'active', 'itinerary_status' => 'draft',
        ]);

        // Send 11 invalid requests (throttle counts regardless of validation)
        // Use unique IP to avoid collision with other tests
        $testIp = '10.99.98.77';
        $url = route('public.services.book', $service->slug);

        for ($i = 0; $i < 10; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => $testIp])
                 ->post($url, []);
        }

        // 11th request → 429
        $this->withServerVariables(['REMOTE_ADDR' => $testIp])
             ->post($url, [])
             ->assertStatus(429);
    }

    // T21 — Admin canTransition enforcement
    public function test_T21_admin_cannot_make_invalid_transition(): void
    {
        // Setup admin user
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin21@example.test',
            'password' => 'p', 'role' => 'super_admin',
        ]);

        // Setup booking in terminal 'completed' state
        $owner = User::create([
            'name' => 'O21', 'email' => 'o21@example.test',
            'password' => 'p', 'role' => 'provider_owner',
        ]);
        $provider = Provider::create([
            'user_id' => $owner->id, 'name' => 'P21', 'slug' => 'p21',
            'verification_status' => 'verified', 'is_active' => true,
        ]);
        $category = ServiceCategory::create(['name' => 'Trek', 'slug' => 'trek']);
        $service = Service::create([
            'provider_id' => $provider->id, 'service_category_id' => $category->id,
            'name' => 'S21', 'slug' => 's21-' . uniqid(),
            'price' => 100, 'currency' => 'USD',
            'status' => 'active', 'itinerary_status' => 'draft',
        ]);
        $traveler = User::create([
            'name' => 'T21', 'email' => 't21@example.test',
            'password' => 'p', 'role' => 'traveler',
        ]);
        $booking = Booking::create([
            'traveler_id' => $traveler->id,
            'service_id'  => $service->id,
            'guest_count' => 1,
            'booking_date' => now(),
            'start_date'   => now()->addDays(5),
            'status'       => 'completed', // terminal
            'qr_code'      => Str::random(32),
            'quota_month'  => \App\Support\QuotaPeriod::current(),
        ]);

        // Attempt invalid transition: completed → pending (should fail)
        $this->actingAs($admin)
             ->post(route('admin.bookings.updateStatus', $booking), [
                 'status' => 'pending',
             ]);

        // Status should remain 'completed'
        $booking->refresh();
        $this->assertEquals('completed', $booking->status);
    }
}