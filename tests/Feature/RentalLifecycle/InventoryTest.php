<?php

namespace Tests\Feature\RentalLifecycle;

use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;
    private Provider $provider;
    private User $buyer;
    private Product $shopProduct;
    private Product $rentalProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->providerUser = User::create([
            'name' => 'Owner', 'email' => 'owner-inv@test.com',
            'password' => 'pass1234', 'role' => 'provider_owner',
        ]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id,
            'name' => 'Inventory Provider',
            'slug' => 'inv-provider-' . uniqid(),
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        $this->buyer = User::create([
            'name' => 'Buyer', 'email' => 'buyer-inv@test.com',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);

        $this->shopProduct = Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'shop',
            'name' => 'Shop Item',
            'slug' => 'shop-item-' . uniqid(),
            'price' => 100,
            'currency' => 'NPR',
            'status' => 'active',
        ]);
        $this->shopProduct->shopDetail()->create(['stock_count' => 10]);

        $this->rentalProduct = Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'rental',
            'name' => 'Rental Item',
            'slug' => 'rental-item-' . uniqid(),
            'price' => 500,
            'currency' => 'NPR',
            'status' => 'active',
        ]);
        $this->rentalProduct->rentalDetail()->create([
            'rental_price_per_day' => 500,
            'rental_deposit'       => 2000,
            'rental_min_days'      => 1,
            'rental_max_days'      => 30,
        ]);
    }

    private function checkout(): void
    {
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);
    }

    public function test_shop_stock_decremented_on_order(): void
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->shopProduct), ['quantity' => 3]);
        $this->checkout();

        $this->shopProduct->shopDetail->refresh();
        $this->assertSame(7, $this->shopProduct->shopDetail->stock_count);
    }

    public function test_shop_stock_restored_on_cancel(): void
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->shopProduct), ['quantity' => 4]);
        $this->checkout();
        $order = Order::latest('id')->first();

        $this->shopProduct->shopDetail->refresh();
        $this->assertSame(6, $this->shopProduct->shopDetail->stock_count);

        // Provider cancels
        $this->actingAs($this->providerUser)->patch(
            route('provider.orders.updateStatus', $order),
            ['status' => 'cancelled']
        );

        $this->shopProduct->shopDetail->refresh();
        $this->assertSame(10, $this->shopProduct->shopDetail->stock_count);
    }

    public function test_checkout_blocks_when_insufficient_stock(): void
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->shopProduct), ['quantity' => 20]);
        // Stock = 10, requested = 20

        $response = $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);

        $response->assertSessionHasErrors('cart');
        $this->assertSame(0, Order::count());
        $this->shopProduct->shopDetail->refresh();
        $this->assertSame(10, $this->shopProduct->shopDetail->stock_count);
    }

    public function test_rental_unavailable_when_dates_overlap(): void
    {
        // First booking: day 1-3
        $this->actingAs($this->buyer)->post(route('cart.add', $this->rentalProduct), [
            'quantity' => 1,
            'rental_start_date' => now()->addDay()->format('Y-m-d'),
            'rental_end_date'   => now()->addDays(3)->format('Y-m-d'),
        ]);
        $this->checkout();
        $this->assertSame(1, Order::count());

        // Second booking: overlapping (day 2-4) — should fail
        $this->actingAs($this->buyer)->post(route('cart.add', $this->rentalProduct), [
            'quantity' => 1,
            'rental_start_date' => now()->addDays(2)->format('Y-m-d'),
            'rental_end_date'   => now()->addDays(4)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);

        $response->assertSessionHasErrors('cart');
        $this->assertSame(1, Order::count());
    }

    public function test_rental_available_when_no_overlap(): void
    {
        // First booking: day 1-3
        $this->actingAs($this->buyer)->post(route('cart.add', $this->rentalProduct), [
            'quantity' => 1,
            'rental_start_date' => now()->addDay()->format('Y-m-d'),
            'rental_end_date'   => now()->addDays(3)->format('Y-m-d'),
        ]);
        $this->checkout();

        // Second booking: non-overlapping (day 10-12) — should succeed
        $this->actingAs($this->buyer)->post(route('cart.add', $this->rentalProduct), [
            'quantity' => 1,
            'rental_start_date' => now()->addDays(10)->format('Y-m-d'),
            'rental_end_date'   => now()->addDays(12)->format('Y-m-d'),
        ]);
        $this->checkout();

        $this->assertSame(2, Order::count());
    }
}