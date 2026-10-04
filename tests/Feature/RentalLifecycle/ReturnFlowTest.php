<?php

namespace Tests\Feature\RentalLifecycle;

use App\Mail\ReturnConfirmedMail;
use App\Mail\ReturnRequestedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReturnFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;
    private Provider $provider;
    private User $buyer;
    private Product $rentalProduct;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->providerUser = User::create([
            'name' => 'Owner', 'email' => 'owner-r@test.com',
            'password' => 'pass1234', 'role' => 'provider_owner',
        ]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id,
            'name' => 'Return Provider',
            'slug' => 'return-provider-' . uniqid(),
            'contact_email' => 'return-provider@test.com',
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        $this->buyer = User::create([
            'name' => 'Buyer', 'email' => 'buyer-r@test.com',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);

        $this->rentalProduct = Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'rental',
            'name' => 'Rental Gear',
            'slug' => 'rental-gear-' . uniqid(),
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
            'lost_deposit_pct'     => 100,
        ]);
    }

    private function makeOrderWithRental(): array
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
        $item = $order->items()->first();
        return [$order, $item];
    }

    public function test_buyer_can_request_return(): void
    {
        [$order, $item] = $this->makeOrderWithRental();
        $response = $this->actingAs($this->buyer)->post(
            route('orders.items.return', [$order, $item])
        );
        $response->assertRedirect();
        $this->assertNotNull($item->fresh()->return_requested_at);
        Mail::assertQueued(ReturnRequestedMail::class);
    }

    public function test_buyer_cannot_request_return_twice(): void
    {
        [$order, $item] = $this->makeOrderWithRental();
        $this->actingAs($this->buyer)->post(route('orders.items.return', [$order, $item]));
        $response = $this->actingAs($this->buyer)->post(route('orders.items.return', [$order, $item]));
        $response->assertSessionHas('info');
    }

    public function test_buyer_cannot_request_return_others_item(): void
    {
        [$order, $item] = $this->makeOrderWithRental();

        $other = User::create([
            'name' => 'X', 'email' => 'x@test.com',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);
        $response = $this->actingAs($other)->post(route('orders.items.return', [$order, $item]));
        $response->assertStatus(403);
    }

    public function test_provider_confirms_return_good_condition(): void
    {
        [$order, $item] = $this->makeOrderWithRental();
        $this->actingAs($this->buyer)->post(route('orders.items.return', [$order, $item]));

        $response = $this->actingAs($this->providerUser)->patch(
            route('provider.orders.items.confirm-return', [$order, $item]),
            ['return_condition' => 'good']
        );

        $response->assertRedirect();
        $item->refresh();
        $this->assertSame('good', $item->return_condition);
        $this->assertSame('2000.00', (string) $item->deposit_refund_amount);
        Mail::assertQueued(ReturnConfirmedMail::class);
    }

    public function test_provider_confirms_return_damaged_deducts(): void
    {
        [$order, $item] = $this->makeOrderWithRental();
        $this->actingAs($this->buyer)->post(route('orders.items.return', [$order, $item]));

        // damage_deposit_pct = 50 → 50% deduction → refund 1000
        $this->actingAs($this->providerUser)->patch(
            route('provider.orders.items.confirm-return', [$order, $item]),
            ['return_condition' => 'damaged']
        );

        $item->refresh();
        $this->assertSame('1000.00', (string) $item->deposit_refund_amount);
    }

    public function test_provider_confirms_return_lost_no_refund(): void
    {
        [$order, $item] = $this->makeOrderWithRental();
        $this->actingAs($this->buyer)->post(route('orders.items.return', [$order, $item]));

        // lost_deposit_pct = 100 → no refund
        $this->actingAs($this->providerUser)->patch(
            route('provider.orders.items.confirm-return', [$order, $item]),
            ['return_condition' => 'lost']
        );

        $item->refresh();
        $this->assertSame('0.00', (string) $item->deposit_refund_amount);
    }

    public function test_late_fee_applied_when_overdue(): void
    {
        // Create order with future dates (passes cart validation)
        $this->actingAs($this->buyer)->post(route('cart.add', $this->rentalProduct), [
            'quantity' => 1,
            'rental_start_date' => now()->addDay()->format('Y-m-d'),
            'rental_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'j@t.com',
        ]);
        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $item = $order->items()->first();

        // Manually mark rental as overdue + return requested
        $item->update([
            'rental_end_date'     => now()->subDays(2),  // 2 days overdue
            'return_requested_at' => now(),
        ]);
        $item->refresh();

        $lateFee = $item->calculateLateFee();
        $this->assertSame(200.0, $lateFee); // 2 days × 100

        // Provider confirms with good condition
        $this->actingAs($this->providerUser)->patch(
            route('provider.orders.items.confirm-return', [$order, $item]),
            ['return_condition' => 'good']
        );

        $item->refresh();
        // Refund = 2000 (good) - 200 (late) = 1800
        $this->assertSame('1800.00', (string) $item->deposit_refund_amount);
    }

    public function test_return_requested_mail_queued(): void
    {
        [$order, $item] = $this->makeOrderWithRental();
        $this->actingAs($this->buyer)->post(route('orders.items.return', [$order, $item]));
        Mail::assertQueued(ReturnRequestedMail::class);
    }

    public function test_return_confirmed_mail_queued(): void
    {
        [$order, $item] = $this->makeOrderWithRental();
        $this->actingAs($this->buyer)->post(route('orders.items.return', [$order, $item]));
        $this->actingAs($this->providerUser)->patch(
            route('provider.orders.items.confirm-return', [$order, $item]),
            ['return_condition' => 'good']
        );
        Mail::assertQueued(ReturnConfirmedMail::class);
    }

    public function test_order_deposit_refunded_total_updated(): void
    {
        [$order, $item] = $this->makeOrderWithRental();
        $this->actingAs($this->buyer)->post(route('orders.items.return', [$order, $item]));
        $this->actingAs($this->providerUser)->patch(
            route('provider.orders.items.confirm-return', [$order, $item]),
            ['return_condition' => 'good']
        );

        $order->refresh();
        $this->assertSame('2000.00', (string) $order->deposit_refunded_amount);
        $this->assertNotNull($order->deposit_refunded_at);
        $this->assertTrue($order->isDepositFullyRefunded());
    }
}