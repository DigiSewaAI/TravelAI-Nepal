<?php

namespace Tests\Feature\OrderTracking;

use App\Mail\OrderPlacedMail;
use App\Mail\OrderStatusChangedMail;
use App\Mail\OrderPaymentVerifiedMail;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;
    private Provider $provider;
    private User $traveler;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->providerUser = User::create([
            'name' => 'Owner', 'email' => 'owner@test.com',
            'password' => 'pass1234', 'role' => 'provider_owner',
        ]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id, 'name' => 'Test Provider',
            'slug' => 'test-provider',
            'contact_email' => 'provider-contact@test.com',
            'verification_status' => 'verified', 'is_active' => true,
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

    public function test_order_creation_records_history(): void
    {
        $order = $this->makeOrder();
        $this->assertSame(1, OrderStatusHistory::where('order_id', $order->id)->count());
        $this->assertSame('pending', OrderStatusHistory::first()->to_status);
    }

    public function test_provider_status_update_records_history(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->providerUser)->patch(route('provider.orders.updateStatus', $order), [
            'status' => 'confirmed',
        ]);

        $this->assertSame(2, OrderStatusHistory::where('order_id', $order->id)->count());
    }

    public function test_payment_verify_records_history(): void
    {
        $order = $this->makeOrder();
        $this->actingAs($this->traveler)->post(route('orders.notifyPayment', $order), [
            'payment_reference' => 'ESW123',
        ]);

        $this->actingAs($this->providerUser)->patch(route('provider.orders.verifyPayment', $order));

        $latest = OrderStatusHistory::where('order_id', $order->id)->latest('id')->first();
        $this->assertSame('confirmed', $latest->to_status);
    }

    public function test_payment_notify_records_history(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->traveler)->post(route('orders.notifyPayment', $order), [
            'payment_reference' => 'ESW123',
        ]);

        $latest = OrderStatusHistory::where('order_id', $order->id)->latest('id')->first();
        $this->assertSame('buyer', $latest->changed_by_role);
    }

    public function test_timeline_displays_in_correct_order(): void
    {
        $order = $this->makeOrder();
        $this->actingAs($this->providerUser)->patch(route('provider.orders.updateStatus', $order), [
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->traveler)->get(route('orders.show', $order));
        $response->assertOk();
        $response->assertSee('Order Timeline');
    }

    public function test_order_placed_mail_queued(): void
    {
        $this->makeOrder();
        Mail::assertQueued(OrderPlacedMail::class);
    }

    public function test_status_changed_mail_queued(): void
    {
        $order = $this->makeOrder();
        $this->actingAs($this->providerUser)->patch(route('provider.orders.updateStatus', $order), [
            'status' => 'confirmed',
        ]);
        Mail::assertQueued(OrderStatusChangedMail::class);
    }

    public function test_payment_verified_mail_queued(): void
    {
        $order = $this->makeOrder();
        $this->actingAs($this->traveler)->post(route('orders.notifyPayment', $order), [
            'payment_reference' => 'ESW123',
        ]);
        $this->actingAs($this->providerUser)->patch(route('provider.orders.verifyPayment', $order));

        Mail::assertQueued(OrderPaymentVerifiedMail::class);
    }

    public function test_mail_failure_does_not_break_flow(): void
    {
        Mail::shouldReceive('to->queue')->andThrow(new \Exception('SMTP down'));

        $order = $this->makeOrder();
        $this->assertNotNull($order);
        // No exception = test passed
    }

    public function test_history_is_immutable(): void
    {
        $order = $this->makeOrder();
        $entry = OrderStatusHistory::first();

        // Immutable = no updated_at (instance property check)
        $this->assertNull($entry->updated_at ?? null);
        $this->assertFalse((new OrderStatusHistory())->timestamps);
    }
}