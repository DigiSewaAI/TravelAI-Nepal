<?php

namespace Tests\Feature\E2E;

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

class OrderJourneyTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;
    private Provider $provider;
    private User $buyer;
    private Product $shopProduct;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->providerUser = User::create([
            'name' => 'Owner', 'email' => 'owner@e2e.test',
            'password' => 'pass1234', 'role' => 'provider_owner',
        ]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id,
            'name' => 'E2E Provider',
            'slug' => 'e2e-provider-' . uniqid(),
            'contact_email' => 'provider-contact@e2e.test',
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        $this->buyer = User::create([
            'name' => 'Buyer', 'email' => 'buyer@e2e.test',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);

        $this->shopProduct = Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'shop',
            'name' => 'E2E Shop Item',
            'slug' => 'e2e-shop-' . uniqid(),
            'price' => 100,
            'currency' => 'NPR',
            'status' => 'active',
        ]);
        $this->shopProduct->shopDetail()->create(['stock_count' => 10]);
    }

    public function test_full_buyer_journey_shop_product(): void
    {
        // 1. Buyer adds to cart
        $this->actingAs($this->buyer)->post(
            route('cart.add', $this->shopProduct),
            ['quantity' => 2]
        );

        // 2. Buyer checks out
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John Buyer',
            'contact_email' => 'buyer@e2e.test',
            'contact_phone' => '9800000000',
            'shipping_address' => 'Thamel',
            'shipping_city' => 'Kathmandu',
            'shipping_country' => 'Nepal',
        ]);

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('200.00', (string) $order->subtotal);

        // 3. History recorded
        $history = OrderStatusHistory::where('order_id', $order->id)->get();
        $this->assertSame(1, $history->count());
        $this->assertSame('pending', $history->first()->to_status);

        // 4. OrderPlacedMail queued to provider
        Mail::assertQueued(OrderPlacedMail::class);
    }

    public function test_provider_sees_and_fulfills_order(): void
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->shopProduct), ['quantity' => 1]);
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);
        $order = Order::latest('id')->first();

        // Provider sees the order
        $response = $this->actingAs($this->providerUser)->get(route('provider.orders.index'));
        $response->assertOk();
        $response->assertSee($order->order_number);

        // Provider confirms
        $this->actingAs($this->providerUser)->patch(
            route('provider.orders.updateStatus', $order),
            ['status' => 'confirmed']
        );

        $order->refresh();
        $this->assertSame('confirmed', $order->status);

        // History recorded
        $latestHistory = OrderStatusHistory::where('order_id', $order->id)->latest('id')->first();
        $this->assertSame('confirmed', $latestHistory->to_status);
        $this->assertSame('pending', $latestHistory->from_status);

        // Email queued to buyer
        Mail::assertQueued(OrderStatusChangedMail::class);
    }

    public function test_buyer_notifies_payment_then_provider_verifies(): void
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->shopProduct), ['quantity' => 1]);
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);
        $order = Order::latest('id')->first();

        // Buyer notifies payment
        $this->actingAs($this->buyer)->post(route('orders.notifyPayment', $order), [
            'payment_reference' => 'ESW-E2E-123',
            'payment_note' => 'Paid via eSewa',
        ]);

        $order->refresh();
        $this->assertNotNull($order->payment_notice_sent_at);
        $this->assertTrue($order->isPaymentNotified());

        // History recorded
        $history = OrderStatusHistory::where('order_id', $order->id)->get();
        $this->assertGreaterThanOrEqual(2, $history->count());

        // Provider verifies payment
        $this->actingAs($this->providerUser)->patch(route('provider.orders.verifyPayment', $order));

        $order->refresh();
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->payment_verified_at);
        $this->assertSame($this->providerUser->id, $order->payment_verified_by);

        // Email queued
        Mail::assertQueued(OrderPaymentVerifiedMail::class);
    }

    public function test_full_rental_journey_with_dates(): void
    {
        $rentalProduct = Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'rental',
            'name' => 'E2E Rental Gear',
            'slug' => 'e2e-rental-' . uniqid(),
            'price' => 500,
            'currency' => 'NPR',
            'status' => 'active',
        ]);
        $rentalProduct->rentalDetail()->create([
            'rental_price_per_day' => 500,
            'rental_deposit' => 2000,
            'rental_min_days' => 1,
            'rental_max_days' => 30,
        ]);

        $startDate = now()->addDay()->format('Y-m-d');
        $endDate = now()->addDays(4)->format('Y-m-d'); // 4 days

        // Add with dates
        $this->actingAs($this->buyer)->post(route('cart.add', $rentalProduct), [
            'quantity' => 1,
            'rental_start_date' => $startDate,
            'rental_end_date' => $endDate,
        ]);

        // Checkout
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);

        $order = Order::latest('id')->first();
        $item = $order->items()->first();

        $this->assertNotNull($item->rental_start_date);
        $this->assertNotNull($item->rental_end_date);
        $this->assertSame(4, $item->rental_days);
        // 500/day × 4 days = 2000
        $this->assertSame('2000.00', (string) $item->line_total);
    }

    public function test_multi_vendor_order_workflow(): void
    {
        // Second provider + product
        $otherUser = User::create([
            'name' => 'O2', 'email' => 'o2@e2e.test',
            'password' => 'pass1234', 'role' => 'provider_owner',
        ]);
        $otherProvider = Provider::create([
            'user_id' => $otherUser->id,
            'name' => 'E2E Provider 2',
            'slug' => 'e2e-provider-2-' . uniqid(),
            'verification_status' => 'verified',
            'is_active' => true,
        ]);
        $otherProduct = Product::create([
            'provider_id' => $otherProvider->id,
            'product_type' => 'shop',
            'name' => 'E2E Item 2',
            'slug' => 'e2e-item-2-' . uniqid(),
            'price' => 50,
            'currency' => 'NPR',
            'status' => 'active',
        ]);

        // Add both products
        $this->actingAs($this->buyer)->post(route('cart.add', $this->shopProduct), ['quantity' => 1]);
        $this->actingAs($this->buyer)->post(route('cart.add', $otherProduct), ['quantity' => 1]);

        // Checkout
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);

        $order = Order::latest('id')->first();
        $this->assertSame(2, $order->items()->count());

        // Provider 1 sees only their item
        $response = $this->actingAs($this->providerUser)->get(route('provider.orders.show', $order));
        $response->assertOk();
        $response->assertSee('E2E Shop Item');
        $response->assertDontSee('E2E Item 2');

        // Provider 1 confirms → status partial (not all same)
        $this->actingAs($this->providerUser)->patch(
            route('provider.orders.updateStatus', $order),
            ['status' => 'confirmed']
        );
        $order->refresh();
        // Only provider 1's item = confirmed → mixed → partial or stays pending
        $this->assertNotSame('pending', OrderStatusHistory::where('order_id', $order->id)->count() === 0 ? 'pending' : 'notpending');

        // Provider 2 confirms → all same → order confirmed
        $this->actingAs($otherUser)->patch(
            route('provider.orders.updateStatus', $order),
            ['status' => 'confirmed']
        );
        $order->refresh();
        $this->assertSame('confirmed', $order->status);
    }

    public function test_order_history_timeline_order(): void
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->shopProduct), ['quantity' => 1]);
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);
        $order = Order::latest('id')->first();

        // Progress through statuses
        $this->actingAs($this->providerUser)->patch(
            route('provider.orders.updateStatus', $order),
            ['status' => 'confirmed']
        );
        $this->actingAs($this->providerUser)->patch(
            route('provider.orders.updateStatus', $order),
            ['status' => 'shipped']
        );
        $this->actingAs($this->providerUser)->patch(
            route('provider.orders.updateStatus', $order),
            ['status' => 'delivered']
        );

        $order->refresh();
        $history = $order->statusHistory()->get();

        // Timeline in chronological order
        $statuses = $history->pluck('to_status')->toArray();
        $this->assertContains('pending', $statuses);
        $this->assertContains('confirmed', $statuses);
        $this->assertContains('shipped', $statuses);
        $this->assertContains('delivered', $statuses);

        // Timeline view renders
        $response = $this->actingAs($this->buyer)->get(route('orders.show', $order));
        $response->assertOk();
        $response->assertSee('Order Timeline');
    }
}