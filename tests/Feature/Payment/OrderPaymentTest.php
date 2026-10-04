<?php

namespace Tests\Feature\Payment;

use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use App\Models\ProviderPaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaymentTest extends TestCase
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

        // Provider payment method
        ProviderPaymentMethod::create([
            'provider_id'    => $this->provider->id,
            'type'           => 'esewa',
            'label'          => 'eSewa',
            'account_name'   => 'Test Provider',
            'account_number' => '9800000001',
            'is_active'      => true,
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

    public function test_order_creates_with_payment_methods_snapshot(): void
    {
        $order = $this->makeOrder();
        $this->assertNotNull($order->payment_methods_snapshot);
        $this->assertArrayHasKey($this->provider->id, $order->payment_methods_snapshot);
        $this->assertSame('Test Provider', $order->payment_methods_snapshot[$this->provider->id]['provider_name']);
    }

    public function test_traveler_can_notify_payment(): void
    {
        $order = $this->makeOrder();

        $response = $this->actingAs($this->traveler)->post(route('orders.notifyPayment', $order), [
            'payment_reference' => 'ESW123456',
            'payment_note'      => 'Paid via eSewa',
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertSame('ESW123456', $order->payment_reference);
        $this->assertNotNull($order->payment_notice_sent_at);
    }

    public function test_traveler_cannot_notify_twice(): void
    {
        $order = $this->makeOrder();
        $this->actingAs($this->traveler)->post(route('orders.notifyPayment', $order), [
            'payment_reference' => 'FIRST',
        ]);

        $response = $this->actingAs($this->traveler)->post(route('orders.notifyPayment', $order), [
            'payment_reference' => 'SECOND',
        ]);

        $response->assertSessionHas('info');
        $this->assertSame('FIRST', $order->fresh()->payment_reference);
    }

    public function test_traveler_cannot_notify_other_order(): void
    {
        $order = $this->makeOrder();

        $other = User::create([
            'name' => 'Other', 'email' => 'other@test.com',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);

        $response = $this->actingAs($other)->post(route('orders.notifyPayment', $order), [
            'payment_reference' => 'X',
        ]);
        $response->assertStatus(403);
    }

    public function test_provider_can_verify_payment(): void
    {
        $order = $this->makeOrder();
        $this->actingAs($this->traveler)->post(route('orders.notifyPayment', $order), [
            'payment_reference' => 'ESW123',
        ]);

        $response = $this->actingAs($this->providerUser)->patch(
            route('provider.orders.verifyPayment', $order)
        );

        $response->assertRedirect();
        $order->refresh();
        $this->assertNotNull($order->payment_verified_at);
        $this->assertSame($this->providerUser->id, $order->payment_verified_by);
    }

    public function test_provider_cannot_verify_other_order(): void
    {
        $other = User::create(['name' => 'O2', 'email' => 'o2@t.com', 'password' => 'pass1234', 'role' => 'provider_owner']);
        $otherP = Provider::create(['user_id' => $other->id, 'name' => 'P2', 'slug' => 'p2-' . uniqid(), 'verification_status' => 'verified', 'is_active' => true]);
        $otherProd = Product::create(['provider_id' => $otherP->id, 'product_type' => 'shop', 'name' => 'X', 'slug' => 'x-' . uniqid(), 'price' => 10, 'currency' => 'NPR', 'status' => 'active']);

        $this->actingAs($this->traveler)->post(route('cart.add', $otherProd), ['quantity' => 1]);
        $this->actingAs($this->traveler)->post(route('checkout.store'), ['contact_name' => 'J', 'contact_email' => 'j@t.com']);
        $order = Order::latest('id')->first();

        $response = $this->actingAs($this->providerUser)->patch(
            route('provider.orders.verifyPayment', $order)
        );
        $response->assertStatus(403);
    }

    public function test_order_status_confirmed_after_verify(): void
    {
        $order = $this->makeOrder();
        $this->actingAs($this->traveler)->post(route('orders.notifyPayment', $order), [
            'payment_reference' => 'ESW',
        ]);
        $this->actingAs($this->providerUser)->patch(route('provider.orders.verifyPayment', $order));

        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_payment_status_paid_after_verify(): void
    {
        $order = $this->makeOrder();
        $this->actingAs($this->traveler)->post(route('orders.notifyPayment', $order), [
            'payment_reference' => 'ESW',
        ]);
        $this->actingAs($this->providerUser)->patch(route('provider.orders.verifyPayment', $order));

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);
    }
}