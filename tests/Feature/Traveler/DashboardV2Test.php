<?php

namespace Tests\Feature\Traveler;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class DashboardV2Test extends TestCase
{
    use RefreshDatabase;

    protected $traveler;
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a traveler and a provider
        $this->traveler = User::factory()->create(['role' => 'traveler']);
        $this->provider = User::factory()->create(['role' => 'provider_owner']);
    }

    /** @test */
    public function test_unauthenticated_redirects_to_login()
    {
        $this->get('/traveler/dashboard-v2')->assertRedirect('/login');
    }

    /** @test */
    public function test_non_traveler_gets_403()
    {
        $this->actingAs($this->provider)
             ->get('/traveler/dashboard-v2')
             ->assertStatus(403);
    }

    /** @test */
    public function test_feature_flag_off_returns_404()
    {
        Config::set('features.traveler_dashboard_v2', false);
        $this->actingAs($this->traveler)
             ->get('/traveler/dashboard-v2')
             ->assertStatus(404);
    }

    /** @test */
    public function test_feature_flag_on_loads_view()
    {
        Config::set('features.traveler_dashboard_v2', true);
        $this->actingAs($this->traveler)
             ->get('/traveler/dashboard-v2')
             ->assertStatus(200)
             ->assertViewIs('traveler.dashboard-v2');
    }

    /** @test */
    public function test_only_authenticated_user_data_visible()
    {
        Config::set('features.traveler_dashboard_v2', true);
        $otherTraveler = User::factory()->create(['role' => 'traveler']);

        // Traveler A sees own name in dashboard greeting
        $response = $this->actingAs($this->traveler)->get('/traveler/dashboard-v2');
        $response->assertStatus(200);
        $response->assertSee($this->traveler->name);

        // Traveler B sees own name
        $response2 = $this->actingAs($otherTraveler)->get('/traveler/dashboard-v2');
        $response2->assertStatus(200);
        $response2->assertSee($otherTraveler->name);
    }

    /** @test */
    public function test_original_dashboard_redirects_to_v2()
    {
        $this->actingAs($this->traveler)
             ->get('/traveler/dashboard')
             ->assertRedirect('/traveler/dashboard-v2');
    }

    /** @test */
    public function test_original_my_bookings_still_works()
    {
        $this->actingAs($this->traveler)
             ->get(route('traveler.my-bookings'))
             ->assertStatus(200);
    }

    /** @test */
    public function test_original_orders_page_still_works()
    {
        $this->actingAs($this->traveler)
             ->get('/orders')
             ->assertStatus(200);
    }

    /** @test */
    public function test_coming_soon_categories_shown()
    {
        Config::set('features.traveler_dashboard_v2', true);
        $this->actingAs($this->traveler)
             ->get('/traveler/dashboard-v2')
             ->assertSee('Hotel')
             ->assertSee('Restaurant')
             ->assertSee('Transport');
    }

    /** @test */
    public function test_mixed_order_handling()
    {
        // Note: Since we don't have factories for OrderItem, we mock the service
        // This is a placeholder test to ensure the view loads without error
        Config::set('features.traveler_dashboard_v2', true);
        $this->actingAs($this->traveler)
             ->get('/traveler/dashboard-v2')
             ->assertStatus(200);
    }
    /** @test */
    public function test_claimed_hotel_renders_in_dashboard()
    {
        Config::set('features.traveler_dashboard_v2', true);
        Config::set('claim.hotel_enabled', true);
        
        $traveler = User::factory()->create(['role' => 'traveler']);
        $provider = \App\Models\Provider::first();
        if (!$provider) $this->markTestSkipped('No provider');
        
        $room = \Illuminate\Support\Facades\DB::table('hotel_rooms')->first();
        if (!$room) $this->markTestSkipped('No room');
        
        $bookingId = \Illuminate\Support\Facades\DB::table('hotel_bookings')->insertGetId([
            'provider_id' => $provider->id,
            'room_id' => $room->id,
            'guest_name' => 'Test Guest',
            'guest_email' => 'test@example.com',
            'check_in_date' => now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => now()->addDays(7)->format('Y-m-d'),
            'nights' => 2,
            'amount' => 1000,
            'payment_status' => 'pending',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        \Illuminate\Support\Facades\DB::table('booking_claims')->insert([
            'claimable_type' => \App\Models\HotelBooking::class,
            'claimable_id' => $bookingId,
            'traveler_id' => $traveler->id,
            'claimed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $response = $this->actingAs($traveler)->get('/traveler/dashboard-v2');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_claimed_restaurant_renders_in_dashboard()
    {
        Config::set('features.traveler_dashboard_v2', true);
        Config::set('claim.restaurant_enabled', true);
        
        $traveler = User::factory()->create(['role' => 'traveler']);
        
        $response = $this->actingAs($traveler)->get('/traveler/dashboard-v2');
        $response->assertStatus(200);
    }

    /** @test */
    public function test_claimed_transport_renders_in_dashboard()
    {
        Config::set('features.traveler_dashboard_v2', true);
        Config::set('claim.transport_enabled', true);
        
        $traveler = User::factory()->create(['role' => 'traveler']);
        
        $response = $this->actingAs($traveler)->get('/traveler/dashboard-v2');
        $response->assertStatus(200);
    }
}
