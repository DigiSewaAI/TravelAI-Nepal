<?php

namespace Tests\Feature\RentalLifecycle;

use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalUiTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;
    private Provider $provider;
    private User $buyer;
    private Product $rentalProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->providerUser = User::create([
            'name' => 'Owner', 'email' => 'owner-ui@test.com',
            'password' => 'pass1234', 'role' => 'provider_owner',
        ]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id,
            'name' => 'UI Provider',
            'slug' => 'ui-provider-' . uniqid(),
            'verification_status' => 'verified',
            'is_active' => true,
        ]);
        $this->buyer = User::create([
            'name' => 'Buyer', 'email' => 'buyer-ui@test.com',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);
        $this->rentalProduct = Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'rental',
            'name' => 'UI Rental',
            'slug' => 'ui-rental-' . uniqid(),
            'price' => 500,
            'currency' => 'NPR',
            'status' => 'active',
        ]);
        $this->rentalProduct->rentalDetail()->create([
            'rental_price_per_day' => 500,
            'rental_deposit'       => 2000,
            'rental_min_days'      => 1,
            'rental_max_days'      => 30,
            'late_fee_per_day'     => 100,
            'damage_deposit_pct'   => 50,
        ]);
    }

    public function test_rental_terms_displays_on_product_page(): void
    {
        $response = $this->get(route('public.products.show', $this->rentalProduct->slug));
        $response->assertOk();
        $response->assertSee(__('messages.rental_select_dates'));
    }

    public function test_rental_duration_hint_displays(): void
    {
        $response = $this->get(route('public.products.show', $this->rentalProduct->slug));
        $response->assertSee(__('messages.rental_duration_hint', ['min' => 1, 'max' => 30]));
    }

    public function test_return_instructions_show_on_active_rental_order(): void
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->rentalProduct), [
            'quantity' => 1,
            'rental_start_date' => now()->addDay()->format('Y-m-d'),
            'rental_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);
        $order = Order::latest('id')->first();

        $response = $this->actingAs($this->buyer)->get(route('orders.show', $order));
        $response->assertOk();
        $response->assertSee(__('messages.rental_return_instructions'));
    }

    public function test_return_instructions_hidden_after_full_refund(): void
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->rentalProduct), [
            'quantity' => 1,
            'rental_start_date' => now()->addDay()->format('Y-m-d'),
            'rental_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);
        $order = Order::latest('id')->first();

        // Fully refund deposit
        $order->update([
            'deposit_refunded_amount' => $order->deposit_total,
            'deposit_refunded_at'     => now(),
        ]);

        $response = $this->actingAs($this->buyer)->get(route('orders.show', $order));
        $response->assertDontSee(__('messages.rental_return_instructions'));
    }

    public function test_rental_terms_shows_late_fee(): void
    {
        $response = $this->get(route('public.products.show', $this->rentalProduct->slug));
        $response->assertSee('NPR 100.00'); // late_fee_per_day = 100
    }
}