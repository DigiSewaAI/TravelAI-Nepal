<?php

namespace Tests\Feature\Cart;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'Test Owner', 'email' => 'owner@example.test',
            'password' => 'password123', 'role' => 'provider_owner',
        ]);

        $this->provider = Provider::create([
            'user_id' => $this->owner->id, 'name' => 'Test Provider',
            'slug' => 'test-provider', 'verification_status' => 'verified', 'is_active' => true,
        ]);
    }

    private function makeProduct(string $type = 'shop', array $overrides = []): Product
    {
        return Product::create(array_merge([
            'provider_id'  => $this->provider->id,
            'product_type' => $type,
            'name'         => "Test {$type}",
            'slug'         => "test-{$type}-" . uniqid(),
            'price'        => 100,
            'currency'     => 'NPR',
            'status'       => 'active',
        ], $overrides));
    }

    public function test_cart_add_shop_product_guest(): void
    {
        $product = $this->makeProduct('shop');

        $response = $this->post(route('cart.add', $product), [
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $this->assertSame(1, Cart::count());
        $this->assertSame(2, Cart::first()->quantity);
    }

    public function test_cart_add_rental_with_dates(): void
    {
        $product = $this->makeProduct('rental');
        $product->rentalDetail()->create([
            'rental_price_per_day' => 50,
            'rental_min_days'      => 1,
            'rental_max_days'      => 30,
        ]);

        $response = $this->post(route('cart.add', $product), [
            'quantity'          => 1,
            'rental_start_date' => now()->addDay()->format('Y-m-d'),
            'rental_end_date'   => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertSame(1, Cart::count());
        $this->assertNotNull(Cart::first()->rental_start_date);
    }

    public function test_cart_rental_dates_required(): void
    {
        $product = $this->makeProduct('rental');
        $product->rentalDetail()->create(['rental_price_per_day' => 50]);

        $response = $this->post(route('cart.add', $product), ['quantity' => 1]);

        $response->assertSessionHas('error');
        $this->assertSame(0, Cart::count());
    }

    public function test_cart_quantity_update(): void
    {
        $product = $this->makeProduct('shop');
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $cart = Cart::first();

        $response = $this->patch(route('cart.update', $cart), ['quantity' => 5]);

        $response->assertRedirect();
        $this->assertSame(5, $cart->fresh()->quantity);
    }

    public function test_cart_remove(): void
    {
        $product = $this->makeProduct('shop');
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $cart = Cart::first();

        $response = $this->delete(route('cart.remove', $cart));

        $response->assertRedirect();
        $this->assertSame(0, Cart::count());
    }

    public function test_cart_guest_session_tracking(): void
    {
        $product = $this->makeProduct('shop');

        $this->post(route('cart.add', $product), ['quantity' => 1]);

        $this->assertNotNull(Cart::first()->session_id);
        $this->assertNull(Cart::first()->user_id);
    }

    public function test_cart_merge_on_login(): void
    {
        $product = $this->makeProduct('shop');

        // Guest adds
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $sessionId = Cart::first()->session_id;

        // Login
        $this->actingAs($this->owner);
        app(\App\Services\CartService::class)->mergeGuestCart($this->owner->id);

        $cart = Cart::first();
        $this->assertSame($this->owner->id, $cart->user_id);
        $this->assertNull($cart->session_id);
    }

    public function test_cart_subtotal_calculation(): void
    {
        $product = $this->makeProduct('shop', ['price' => 100]);
        $this->post(route('cart.add', $product), ['quantity' => 3]);

        $cart = Cart::first();
        $this->assertSame(300.0, (float) $cart->subtotal);
    }
}