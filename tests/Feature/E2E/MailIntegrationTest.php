<?php

namespace Tests\Feature\E2E;

use App\Mail\OrderPlacedMail;
use App\Mail\OrderStatusChangedMail;
use App\Mail\OrderPaymentVerifiedMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;
    private Provider $provider;
    private User $buyer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->providerUser = User::create([
            'name' => 'Owner', 'email' => 'owner-mail@e2e.test',
            'password' => 'pass1234', 'role' => 'provider_owner',
        ]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id,
            'name' => 'Mail Provider',
            'slug' => 'mail-provider-' . uniqid(),
            'contact_email' => 'mail-provider@e2e.test',
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        $this->buyer = User::create([
            'name' => 'Buyer', 'email' => 'buyer-mail@e2e.test',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);

        $this->product = Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'shop',
            'name' => 'Mail Test Product',
            'slug' => 'mail-test-' . uniqid(),
            'price' => 100,
            'currency' => 'NPR',
            'status' => 'active',
        ]);
    }

    private function makeOrder(): Order
    {
        $this->actingAs($this->buyer)->post(route('cart.add', $this->product), ['quantity' => 1]);
        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'contact_name' => 'John', 'contact_email' => 'buyer-mail@e2e.test',
        ]);
        return Order::latest('id')->first();
    }

    public function test_all_order_emails_queued_correctly(): void
    {
        Mail::fake();

        // 1. Order placed → OrderPlacedMail
        $order = $this->makeOrder();
        Mail::assertQueued(OrderPlacedMail::class);

        // 2. Status change → OrderStatusChangedMail
        $this->actingAs($this->providerUser)->patch(
            route('provider.orders.updateStatus', $order),
            ['status' => 'confirmed']
        );
        Mail::assertQueued(OrderStatusChangedMail::class);

        // 3. Payment verified → OrderPaymentVerifiedMail
        $this->actingAs($this->buyer)->post(route('orders.notifyPayment', $order), [
            'payment_reference' => 'ESW-MAIL-01',
        ]);
        $this->actingAs($this->providerUser)->patch(route('provider.orders.verifyPayment', $order));
        Mail::assertQueued(OrderPaymentVerifiedMail::class);
    }

    public function test_mail_failure_does_not_break_order_flow(): void
    {
        // Force Mail::queue to throw
        Mail::shouldReceive('to')->andThrow(new \Exception('SMTP down'));

        // Order should still be created despite mail failure
        try {
            $order = $this->makeOrder();
            $this->assertNotNull($order);
            $this->assertSame(1, Order::count());
        } catch (\Throwable $e) {
            $this->fail('Order creation broke due to mail failure: ' . $e->getMessage());
        }
    }

    public function test_no_emails_sent_to_wrong_users(): void
    {
        Mail::fake();

        // Other buyer
        $otherBuyer = User::create([
            'name' => 'Other Buyer', 'email' => 'other-buyer@e2e.test',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);

        // Buyer places order
        $order = $this->makeOrder();

        // Provider confirms → email should go to CORRECT buyer, not other
        $this->actingAs($this->providerUser)->patch(
            route('provider.orders.updateStatus', $order),
            ['status' => 'confirmed']
        );

        // Assert mail was queued (to correct buyer — verify via order relation)
        Mail::assertQueued(OrderStatusChangedMail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id
                && $mail->order->user_id === $this->buyer->id;
        });
    }
}