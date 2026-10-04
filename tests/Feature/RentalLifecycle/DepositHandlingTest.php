<?php

namespace Tests\Feature\RentalLifecycle;

use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositHandlingTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;
    private Provider $provider;
    private User $buyer;
    private Product $rentalProduct;
    private Product $shopProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->providerUser = User::create([
            'name' => 'Owner', 'email' => 'owner-dep@test.com',
            'password' => 'pass1234', 'role' => 'provider_owner',
        ]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id,
            'name' => 'Deposit Provider',
            'slug' => 'dep-provider-' . uniqid(),
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        $this->buyer = User::create([
            'name' => 'Buyer', 'email' => 'buyer-dep@test.com',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);

        $this->rentalProduct = Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'rental',
            'name' => 'Down Jacket',
            'slug' => 'down-jacket-' . uniqid(),
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

        $this->shopProduct = Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'shop',
            'name' => 'Souvenir',
            'slug' => 'souvenir-' . uniqid(),
            'price' => 100,
            'currency' => 'NPR',
            'status' => 'active',
        ]);
    }

    private function checkout(): Order
    {
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);
        return Order::latest('id')->first();
    }

    public function test_checkout_adds_deposit_to_total(): void
    {
        // 3 days rental, price/day 500, deposit 2000
        $this->actingAs($this->buyer)->post(route('cart.add', $this->rentalProduct), [
            'quantity' => 1,
            'rental_start_date' => now()->addDay()->format('Y-m-d'),
            'rental_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $order = $this->checkout();

        // rental = 500 × 3 = 1500; deposit = 2000
        $this->assertSame('1500.00', (string) $order->subtotal);
        $this->assertSame('2000.00', (string) $order->deposit_total);
        $this->assertSame('3500.00', (string) $order->total);
    }

    public function test_deposit_total_zero_for_non_rental_orders(): void
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->shopProduct), ['quantity' => 2]);
        $order = $this->checkout();

        $this->assertSame('0.00', (string) $order->deposit_total);
        $this->assertSame('200.00', (string) $order->total);
    }

    public function test_deposit_held_attribute_correct(): void
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->rentalProduct), [
            'quantity' => 1,
            'rental_start_date' => now()->addDay()->format('Y-m-d'),
            'rental_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);
        $order = $this->checkout();

        // Initially held = deposit_total
        $this->assertSame(2000.0, $order->deposit_held);

        // Partial refund scenario
        $order->update(['deposit_refunded_amount' => 500]);
        $this->assertSame(1500.0, $order->fresh()->deposit_held);
    }

    public function test_has_rental_items_helper(): void
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->rentalProduct), [
            'quantity' => 1,
            'rental_start_date' => now()->addDay()->format('Y-m-d'),
            'rental_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);
        $order = $this->checkout();
        $this->assertTrue($order->hasRentalItems());

        // Non-rental order
        $this->actingAs($this->buyer)->post(route('cart.add', $this->shopProduct), ['quantity' => 1]);
        $shopOrder = $this->checkout();
        $this->assertFalse($shopOrder->hasRentalItems());
    }

    public function test_deposit_included_in_order_total_rental(): void
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->rentalProduct), [
            'quantity' => 2, // 2 units
            'rental_start_date' => now()->addDay()->format('Y-m-d'),
            'rental_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $order = $this->checkout();

        // rental = 500 × 3 × 2 = 3000; deposit = 2000 × 2 = 4000; total = 7000
        $this->assertSame('3000.00', (string) $order->subtotal);
        $this->assertSame('4000.00', (string) $order->deposit_total);
        $this->assertSame('7000.00', (string) $order->total);
    }

    public function test_cart_shows_deposit_line(): void
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->rentalProduct), [
            'quantity' => 1,
            'rental_start_date' => now()->addDay()->format('Y-m-d'),
            'rental_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->buyer)->get(route('cart.index'));
        $response->assertOk();
        $response->assertSee('Refundable deposit');
    }
}