<?php

namespace Tests\Feature\Provider;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;
    private Provider $provider;
    private User $traveler;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->providerUser = User::create([
            'name' => 'Owner', 'email' => 'owner@test.com',
            'password' => 'pass1234', 'role' => 'provider_owner',
        ]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id, 'name' => 'Test Provider',
            'slug' => 'test-provider', 'verification_status' => 'verified', 'is_active' => true,
        ]);

        $this->traveler = User::create([
            'name' => 'Traveler', 'email' => 'traveler@test.com',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);

        $this->product = Product::create([
            'provider_id' => $this->provider->id, 'product_type' => 'shop',
            'name' => 'Test Product', 'slug' => 'test-' . uniqid(),
            'price' => 100, 'currency' => 'NPR', 'status' => 'active',
        ]);
    }

    private function makeOrder(): Order
    {
        $this->actingAs($this->traveler)->post(route('cart.add', $this->product), ['quantity' => 1]);

        $this->actingAs($this->traveler)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);

        return Order::latest('id')->first();
    }

    public function test_provider_sees_their_orders(): void
    {
        $order = $this->makeOrder();

        $response = $this->actingAs($this->providerUser)->get(route('provider.orders.index'));
        $response->assertOk();
        $response->assertSee($order->order_number);
    }

    public function test_provider_cannot_see_others_order(): void
    {
        // Create different provider + order
        $other = User::create(['name' => 'Other', 'email' => 'o@t.com', 'password' => 'pass1234', 'role' => 'provider_owner']);
        $otherP = Provider::create(['user_id' => $other->id, 'name' => 'Other P', 'slug' => 'other-p-' . uniqid(), 'verification_status' => 'verified', 'is_active' => true]);
        $otherProd = Product::create(['provider_id' => $otherP->id, 'product_type' => 'shop', 'name' => 'X', 'slug' => 'x-' . uniqid(), 'price' => 10, 'currency' => 'NPR', 'status' => 'active']);

        $this->actingAs($this->traveler)->post(route('cart.add', $otherProd), ['quantity' => 1]);
        $this->actingAs($this->traveler)->post(route('checkout.store'), ['contact_name' => 'J', 'contact_email' => 'j@t.com']);
        $order = Order::latest('id')->first();

        $response = $this->actingAs($this->providerUser)->get(route('provider.orders.show', $order));
        $response->assertStatus(403);
    }

    public function test_provider_updates_item_status(): void
    {
        $order = $this->makeOrder();

        $response = $this->actingAs($this->providerUser)->patch(route('provider.orders.updateStatus', $order), [
            'status' => 'confirmed',
        ]);

        $response->assertRedirect();
        $this->assertSame('confirmed', $order->items()->first()->provider_status);
    }

    public function test_order_aggregate_status_updated(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->providerUser)->patch(route('provider.orders.updateStatus', $order), [
            'status' => 'confirmed',
        ]);

        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_provider_sees_only_their_items_in_multivendor(): void
    {
        // Multi-vendor setup
        $other = User::create(['name' => 'O2', 'email' => 'o2@t.com', 'password' => 'pass1234', 'role' => 'provider_owner']);
        $otherP = Provider::create(['user_id' => $other->id, 'name' => 'P2', 'slug' => 'p2-' . uniqid(), 'verification_status' => 'verified', 'is_active' => true]);
        $otherProd = Product::create(['provider_id' => $otherP->id, 'product_type' => 'shop', 'name' => 'X2', 'slug' => 'x2-' . uniqid(), 'price' => 50, 'currency' => 'NPR', 'status' => 'active']);

        $this->actingAs($this->traveler)->post(route('cart.add', $this->product), ['quantity' => 1]);
        $this->actingAs($this->traveler)->post(route('cart.add', $otherProd), ['quantity' => 1]);
        $this->actingAs($this->traveler)->post(route('checkout.store'), ['contact_name' => 'J', 'contact_email' => 'j@t.com']);

        $order = Order::latest('id')->first();

        $response = $this->actingAs($this->providerUser)->get(route('provider.orders.show', $order));
        $response->assertOk();
        $response->assertSee('Test Product');
        $response->assertDontSee('X2');
    }

    public function test_provider_status_update_403_if_no_items(): void
    {
        $other = User::create(['name' => 'O3', 'email' => 'o3@t.com', 'password' => 'pass1234', 'role' => 'provider_owner']);
        $otherP = Provider::create(['user_id' => $other->id, 'name' => 'P3', 'slug' => 'p3-' . uniqid(), 'verification_status' => 'verified', 'is_active' => true]);
        $otherProd = Product::create(['provider_id' => $otherP->id, 'product_type' => 'shop', 'name' => 'X3', 'slug' => 'x3-' . uniqid(), 'price' => 10, 'currency' => 'NPR', 'status' => 'active']);

        $this->actingAs($this->traveler)->post(route('cart.add', $otherProd), ['quantity' => 1]);
        $this->actingAs($this->traveler)->post(route('checkout.store'), ['contact_name' => 'J', 'contact_email' => 'j@t.com']);
        $order = Order::latest('id')->first();

        $response = $this->actingAs($this->providerUser)->patch(route('provider.orders.updateStatus', $order), [
            'status' => 'confirmed',
        ]);
        $response->assertStatus(403);
    }

    public function test_provider_order_filter_by_status(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->providerUser)->patch(route('provider.orders.updateStatus', $order), [
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->providerUser)->get(route('provider.orders.index', ['status' => 'confirmed']));
        $response->assertOk();
        $response->assertSee($order->order_number);
    }

    public function test_order_item_provider_status_default(): void
    {
        $order = $this->makeOrder();
        $this->assertSame('pending', $order->items()->first()->provider_status);
    }
}