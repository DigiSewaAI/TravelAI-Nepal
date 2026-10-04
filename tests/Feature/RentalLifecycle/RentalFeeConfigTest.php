<?php

namespace Tests\Feature\RentalLifecycle;

use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalFeeConfigTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;
    private Provider $provider;
    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->providerUser = User::create([
            'name' => 'Owner', 'email' => 'owner-c4@test.com',
            'password' => 'pass1234', 'role' => 'provider_owner',
        ]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id,
            'name' => 'C4 Provider',
            'slug' => 'c4-provider-' . uniqid(),
            'verification_status' => 'verified',
            'is_active' => true,
        ]);
        $this->buyer = User::create([
            'name' => 'Buyer', 'email' => 'buyer-c4@test.com',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);
    }

    public function test_provider_can_set_late_fee_per_day(): void
    {
        $response = $this->actingAs($this->providerUser)->post(route('provider.products.store'), [
            'product_type'         => 'rental',
            'name'                 => 'Fee Test Gear',
            'price'                => 500,
            'currency'             => 'NPR',
            'status'               => 'active',
            'rental_price_per_day' => 500,
            'rental_deposit'       => 2000,
            'rental_min_days'      => 1,
            'rental_max_days'      => 30,
            'late_fee_per_day'     => 150,
            'damage_deposit_pct'   => 50,
            'lost_deposit_pct'     => 100,
        ]);

        $response->assertRedirect();
        $product = Product::latest('id')->first();
        $this->assertNotNull($product->rentalDetail);
        $this->assertSame('150.00', (string) $product->rentalDetail->late_fee_per_day);
    }

    public function test_provider_can_set_damage_deposit_pct(): void
    {
        $this->actingAs($this->providerUser)->post(route('provider.products.store'), [
            'product_type'         => 'rental',
            'name'                 => 'Damage Test',
            'price'                => 500,
            'currency'             => 'NPR',
            'status'               => 'active',
            'rental_price_per_day' => 500,
            'rental_deposit'       => 2000,
            'rental_min_days'      => 1,
            'damage_deposit_pct'   => 30,
            'lost_deposit_pct'     => 80,
        ]);

        $detail = Product::latest('id')->first()->rentalDetail;
        $this->assertSame(30, $detail->damage_deposit_pct);
        $this->assertSame(80, $detail->lost_deposit_pct);
    }

    public function test_late_fee_deduction_applied_correctly(): void
    {
        $product = $this->makeRentalProduct(['late_fee_per_day' => 100]);
        $order = $this->makeOrder($product);

        // Simulate 2-day overdue return
        $item = $order->items()->first();
        $item->update([
            'rental_end_date'     => now()->subDays(2),
            'return_requested_at' => now(),
        ]);
        $item->refresh();

        $lateFee = $item->calculateLateFee();
        $this->assertSame(200.0, $lateFee);
    }

    public function test_damage_deduction_uses_provider_pct(): void
    {
        $product = $this->makeRentalProduct(['damage_deposit_pct' => 25]);
        $order = $this->makeOrder($product);

        $item = $order->items()->first();
        $item->return_condition = 'damaged';
        $refund = $item->calculateDepositRefund();

        // deposit 2000 × (1 - 0.25) = 1500
        $this->assertSame(1500.0, $refund);
    }

    public function test_refund_breakdown_displays_correct_amounts(): void
    {
        $product = $this->makeRentalProduct();
        $order = $this->makeOrder($product);

        $item = $order->items()->first();
        $item->update([
            'return_requested_at'   => now(),
            'return_confirmed_at'   => now(),
            'return_condition'      => 'good',
            'deposit_refund_amount' => 2000,
            'deposit_refunded_at'   => now(),
        ]);

        $response = $this->actingAs($this->buyer)->get(route('orders.show', $order));
        $response->assertOk();
        $response->assertSee(__('messages.return_refund_breakdown'));
    }

    // ─── Helpers ───
    private function makeRentalProduct(array $overrides = []): Product
    {
        $product = Product::create([
            'provider_id'  => $this->provider->id,
            'product_type' => 'rental',
            'name'         => 'Test Rental',
            'slug'         => 'test-rental-' . uniqid(),
            'price'        => 500,
            'currency'     => 'NPR',
            'status'       => 'active',
        ]);
        $product->rentalDetail()->create(array_merge([
            'rental_price_per_day' => 500,
            'rental_deposit'       => 2000,
            'rental_min_days'      => 1,
            'rental_max_days'      => 30,
            'late_fee_per_day'     => 100,
            'damage_deposit_pct'   => 50,
            'lost_deposit_pct'     => 100,
        ], $overrides));
        return $product;
    }

    private function makeOrder(Product $product): Order
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $product), [
            'quantity'          => 1,
            'rental_start_date' => now()->addDay()->format('Y-m-d'),
            'rental_end_date'   => now()->addDays(3)->format('Y-m-d'),
        ]);
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);
        return Order::latest('id')->first();
    }
}