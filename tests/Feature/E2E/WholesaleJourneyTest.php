<?php

namespace Tests\Feature\E2E;

use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use App\Models\WholesaleRfq;
use App\Models\WholesaleRfqMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WholesaleJourneyTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;
    private Provider $provider;
    private User $buyer;
    private Product $wholesaleProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->providerUser = User::create([
            'name' => 'WS Owner', 'email' => 'ws-owner@e2e.test',
            'password' => 'pass1234', 'role' => 'provider_owner',
        ]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id,
            'name' => 'WS Provider',
            'slug' => 'ws-provider-' . uniqid(),
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        $this->buyer = User::create([
            'name' => 'WS Buyer', 'email' => 'ws-buyer@e2e.test',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);

        $this->wholesaleProduct = Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'wholesale',
            'name' => 'E2E Bulk Pashmina',
            'slug' => 'e2e-bulk-' . uniqid(),
            'price' => 500,
            'currency' => 'NPR',
            'status' => 'active',
        ]);
        $this->wholesaleProduct->wholesaleDetail()->create([
            'min_order_qty' => 10,
        ]);
    }

    public function test_full_wholesale_rfq_journey(): void
    {
        // 1. Buyer creates RFQ
        $this->actingAs($this->buyer)->post(
            route('wholesale.rfq.store', $this->wholesaleProduct),
            [
                'quantity' => 50,
                'buyer_company' => 'E2E Retailer Co',
                'buyer_phone' => '9800000000',
                'message' => 'Need 50 units for retail.',
            ]
        );

        $rfq = WholesaleRfq::latest('id')->first();
        $this->assertNotNull($rfq);
        $this->assertSame('pending', $rfq->status);
        $this->assertMatchesRegularExpression('/^RFQ-\d{2}-\d{5}$/', $rfq->rfq_number);

        // 2. Provider sees RFQ in inbox
        $response = $this->actingAs($this->providerUser)->get(route('provider.wholesale-rfq.index'));
        $response->assertOk();
        $response->assertSee($rfq->rfq_number);

        // 3. Provider quotes
        $this->actingAs($this->providerUser)->patch(
            route('provider.wholesale-rfq.quote', $rfq),
            [
                'quoted_price' => 450,
                'provider_response' => 'Best price for bulk.',
                'valid_until' => now()->addDays(7)->format('Y-m-d'),
            ]
        );

        $rfq->refresh();
        $this->assertSame('quoted', $rfq->status);
        $this->assertSame('450.00', (string) $rfq->quoted_price);
        $this->assertSame('22500.00', (string) $rfq->quoted_total); // 450 × 50

        // 4. Buyer sees quote
        $response = $this->actingAs($this->buyer)->get(route('wholesale.rfq.show', $rfq));
        $response->assertOk();
        $response->assertSee($rfq->rfq_number);

        // 5. Buyer accepts
        $this->actingAs($this->buyer)->post(route('wholesale.rfq.accept', $rfq));

        $rfq->refresh();
        $this->assertSame('accepted', $rfq->status);
        $this->assertNotNull($rfq->accepted_at);
    }

    public function test_wholesale_rfq_threaded_messages(): void
    {
        $this->actingAs($this->buyer)->post(
            route('wholesale.rfq.store', $this->wholesaleProduct),
            ['quantity' => 20, 'message' => 'Initial inquiry']
        );
        $rfq = WholesaleRfq::latest('id')->first();

        // Buyer message
        $this->actingAs($this->buyer)->post(
            route('wholesale.rfq.message', $rfq),
            ['message' => 'Can you ship to Pokhara?']
        );

        // Provider reply
        $this->actingAs($this->providerUser)->post(
            route('provider.wholesale-rfq.message', $rfq),
            ['message' => 'Yes, shipping available.']
        );

        // Buyer follow-up
        $this->actingAs($this->buyer)->post(
            route('wholesale.rfq.message', $rfq),
            ['message' => 'Great, thanks!']
        );

        $messages = WholesaleRfqMessage::where('rfq_id', $rfq->id)
            ->orderBy('id', 'asc')
            ->get();

        $this->assertSame(3, $messages->count());
        $this->assertSame('buyer', $messages[0]->sender_role);
        $this->assertSame('provider', $messages[1]->sender_role);
        $this->assertSame('buyer', $messages[2]->sender_role);

        // Both parties see full thread
        $buyerView = $this->actingAs($this->buyer)->get(route('wholesale.rfq.show', $rfq));
        $buyerView->assertOk();
        $buyerView->assertSee('Can you ship to Pokhara?');

        $providerView = $this->actingAs($this->providerUser)->get(route('provider.wholesale-rfq.show', $rfq));
        $providerView->assertOk();
        $providerView->assertSee('Yes, shipping available.');
    }

    public function test_wholesale_rfq_rejection_flow(): void
    {
        $this->actingAs($this->buyer)->post(
            route('wholesale.rfq.store', $this->wholesaleProduct),
            ['quantity' => 20]
        );
        $rfq = WholesaleRfq::latest('id')->first();

        // Provider quotes
        $this->actingAs($this->providerUser)->patch(
            route('provider.wholesale-rfq.quote', $rfq),
            ['quoted_price' => 500]
        );

        // Buyer rejects
        $this->actingAs($this->buyer)->post(route('wholesale.rfq.reject', $rfq));

        $rfq->refresh();
        $this->assertSame('rejected', $rfq->status);
        $this->assertNotNull($rfq->rejected_at);

        // Further messages blocked
        $response = $this->actingAs($this->buyer)->post(
            route('wholesale.rfq.message', $rfq),
            ['message' => 'Wait, reconsidering...']
        );
        $response->assertSessionHas('error');
    }
}