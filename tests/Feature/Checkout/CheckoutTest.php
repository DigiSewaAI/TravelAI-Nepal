<?php

namespace Tests\Feature\Checkout;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $traveler;
    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'Owner', 'email' => 'owner@test.com',
            'password' => 'pass1234', 'role' => 'provider_owner',
        ]);

        $this->provider = Provider::create([
            'user_id' => $this->owner->id, 'name' => 'Test Provider',
            'slug' => 'test-provider', 'verification_status' => 'verified', 'is_active' => true,
        ]);

        $this->traveler = User::create([
            'name' => 'Traveler', 'email' => 'traveler@test.com',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);
    }

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'provider_id' => $this->provider->id, 'product_type' => 'shop',
            'name' => 'Test Product', 'slug' => 'test-' . uniqid(),
            'price' => 100, 'currency' => 'NPR', 'status' => 'active',
        ], $overrides));
    }

    private function addToCart(Product $product, int $qty = 1): void
    {
        $this->actingAs($this->traveler)->post(route('cart.add', $product), ['quantity' => $qty]);
    }

    public function test_checkout_requires_auth(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_checkout_empty_cart_redirect(): void
    {
        $response = $this->actingAs($this->traveler)->get(route('checkout.index'));
        $response->assertRedirect(route('cart.index'));
    }

    public function test_checkout_creates_order_with_items(): void
    {
        $product = $this->makeProduct();
        $this->addToCart($product, 2);

        $response = $this->actingAs($this->traveler)->post(route('checkout.store'), [
            'contact_name'  => 'John Doe',
            'contact_email' => 'john@test.com',
            'contact_phone' => '9800000000',
        ]);

        $order = Order::first();
        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame(1, Order::count());
        $this->assertSame(1, $order->items()->count());
    }

    public function test_order_number_generated(): void
    {
        $product = $this->makeProduct();
        $this->addToCart($product);

        $this->actingAs($this->traveler)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);

        $order = Order::first();
        $this->assertMatchesRegularExpression('/^ORD-\d{2}-\d{5}$/', $order->order_number);
    }

    public function test_order_items_snapshot_product_data(): void
    {
        $product = $this->makeProduct(['name' => 'Original Name', 'price' => 250]);
        $this->addToCart($product, 3);

        $this->actingAs($this->traveler)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);

        // Change product after order (should not affect snapshot)
        $product->update(['name' => 'Changed', 'price' => 999]);

        $item = Order::first()->items()->first();
        $this->assertSame('Original Name', $item->product_name);
        $this->assertSame('250.00', (string) $item->unit_price);
    }

    public function test_order_total_calculation(): void
    {
        $p1 = $this->makeProduct(['price' => 100]);
        $p2 = $this->makeProduct(['price' => 50]);

        $this->addToCart($p1, 2); // 200
        $this->addToCart($p2, 3); // 150

        $this->actingAs($this->traveler)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);

        $order = Order::first();
        $this->assertSame('350.00', (string) $order->subtotal);
        $this->assertSame('350.00', (string) $order->total);
    }

    public function test_order_history_lists_user_orders(): void
    {
        $product = $this->makeProduct();
        $this->addToCart($product);
        $this->actingAs($this->traveler)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);

        $response = $this->actingAs($this->traveler)->get(route('orders.index'));
        $response->assertOk();
        $response->assertSee(Order::first()->order_number);
    }

    public function test_order_detail_ownership_check(): void
    {
        $product = $this->makeProduct();
        $this->addToCart($product);
        $this->actingAs($this->traveler)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);
        $order = Order::first();

        $otherUser = User::create([
            'name' => 'Other', 'email' => 'other@test.com',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);

        $response = $this->actingAs($otherUser)->get(route('orders.show', $order));
        $response->assertStatus(403);
    }

    public function test_cart_cleared_after_checkout(): void
    {
        $product = $this->makeProduct();
        $this->addToCart($product, 2);
        $this->assertSame(1, Cart::count());

        $this->actingAs($this->traveler)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);

        $this->assertSame(0, Cart::count());
    }

    public function test_rental_days_calculated(): void
    {
        $product = $this->makeProduct(['product_type' => 'rental']);
        $product->rentalDetail()->create(['rental_price_per_day' => 100]);

        $start = now()->addDay()->format('Y-m-d');
        $end = now()->addDays(4)->format('Y-m-d'); // 4 days total (diff=3 + 1)

        $this->actingAs($this->traveler)->post(route('cart.add', $product), [
            'quantity' => 1,
            'rental_start_date' => $start,
            'rental_end_date'   => $end,
        ]);

        $this->actingAs($this->traveler)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);

        $item = Order::first()->items()->first();
        $this->assertSame(4, $item->rental_days);
    }
}