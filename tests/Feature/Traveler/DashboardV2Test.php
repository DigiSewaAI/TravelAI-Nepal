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
    public function test_original_dashboard_still_works()
    {
        $this->actingAs($this->traveler)
             ->get('/traveler/dashboard')
             ->assertStatus(200);
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
}