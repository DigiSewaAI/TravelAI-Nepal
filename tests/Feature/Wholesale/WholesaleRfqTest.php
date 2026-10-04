<?php

namespace Tests\Feature\Wholesale;

use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use App\Models\WholesaleRfq;
use App\Models\WholesaleRfqMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WholesaleRfqTest extends TestCase
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
            'name' => 'Owner', 'email' => 'owner@test.com',
            'password' => 'pass1234', 'role' => 'provider_owner',
        ]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id, 'name' => 'Test Provider',
            'slug' => 'test-provider', 'verification_status' => 'verified', 'is_active' => true,
        ]);

        $this->buyer = User::create([
            'name' => 'Buyer', 'email' => 'buyer@test.com',
            'password' => 'pass1234', 'role' => 'traveler',
        ]);

        $this->product = Product::create([
            'provider_id' => $this->provider->id, 'product_type' => 'wholesale',
            'name' => 'Bulk Pashmina', 'slug' => 'bulk-pashmina-' . uniqid(),
            'price' => 500, 'currency' => 'NPR', 'status' => 'active',
        ]);
        $this->product->wholesaleDetail()->create(['min_order_qty' => 10]);
    }

    public function test_buyer_creates_rfq(): void
    {
        $response = $this->actingAs($this->buyer)->post(
            route('wholesale.rfq.store', $this->product),
            ['quantity' => 20, 'buyer_company' => 'Retailer Co', 'message' => 'Need 20 units']
        );

        $response->assertRedirect();
        $this->assertSame(1, WholesaleRfq::count());
        $this->assertSame(20, WholesaleRfq::first()->quantity);
    }

    public function test_rfq_number_generated(): void
    {
        $this->actingAs($this->buyer)->post(route('wholesale.rfq.store', $this->product), ['quantity' => 20]);

        $rfq = WholesaleRfq::first();
        $this->assertMatchesRegularExpression('/^RFQ-\d{2}-\d{5}$/', $rfq->rfq_number);
    }

    public function test_rfq_requires_min_order_qty(): void
    {
        $response = $this->actingAs($this->buyer)->post(
            route('wholesale.rfq.store', $this->product),
            ['quantity' => 5]  // below min 10
        );

        $response->assertSessionHasErrors('quantity');
        $this->assertSame(0, WholesaleRfq::count());
    }

    public function test_provider_sees_only_their_rfqs(): void
    {
        $this->actingAs($this->buyer)->post(route('wholesale.rfq.store', $this->product), ['quantity' => 20]);

        // Different provider
        $other = User::create(['name' => 'O', 'email' => 'o@t.com', 'password' => 'pass1234', 'role' => 'provider_owner']);
        Provider::create(['user_id' => $other->id, 'name' => 'P2', 'slug' => 'p2-' . uniqid(), 'verification_status' => 'verified', 'is_active' => true]);

        $response = $this->actingAs($other)->get(route('provider.wholesale-rfq.index'));
        $response->assertOk();
        $response->assertDontSee(WholesaleRfq::first()->rfq_number);
    }

    public function test_provider_can_quote_rfq(): void
    {
        $this->actingAs($this->buyer)->post(route('wholesale.rfq.store', $this->product), ['quantity' => 20]);
        $rfq = WholesaleRfq::first();

        $response = $this->actingAs($this->providerUser)->patch(
            route('provider.wholesale-rfq.quote', $rfq),
            ['quoted_price' => 450]
        );

        $response->assertRedirect();
        $this->assertSame('quoted', $rfq->fresh()->status);
    }

    public function test_quote_sets_status_and_total(): void
    {
        $this->actingAs($this->buyer)->post(route('wholesale.rfq.store', $this->product), ['quantity' => 20]);
        $rfq = WholesaleRfq::first();

        $this->actingAs($this->providerUser)->patch(
            route('provider.wholesale-rfq.quote', $rfq),
            ['quoted_price' => 450]
        );

        $rfq->refresh();
        $this->assertSame('450.00', (string) $rfq->quoted_price);
        $this->assertSame('9000.00', (string) $rfq->quoted_total); // 450 × 20
    }

    public function test_buyer_can_accept_quote(): void
    {
        $this->actingAs($this->buyer)->post(route('wholesale.rfq.store', $this->product), ['quantity' => 20]);
        $rfq = WholesaleRfq::first();
        $this->actingAs($this->providerUser)->patch(route('provider.wholesale-rfq.quote', $rfq), ['quoted_price' => 450]);

        $response = $this->actingAs($this->buyer)->post(route('wholesale.rfq.accept', $rfq));

        $response->assertRedirect();
        $this->assertSame('accepted', $rfq->fresh()->status);
        $this->assertNotNull($rfq->fresh()->accepted_at);
    }

    public function test_buyer_can_reject_quote(): void
    {
        $this->actingAs($this->buyer)->post(route('wholesale.rfq.store', $this->product), ['quantity' => 20]);
        $rfq = WholesaleRfq::first();
        $this->actingAs($this->providerUser)->patch(route('provider.wholesale-rfq.quote', $rfq), ['quoted_price' => 450]);

        $response = $this->actingAs($this->buyer)->post(route('wholesale.rfq.reject', $rfq));

        $response->assertRedirect();
        $this->assertSame('rejected', $rfq->fresh()->status);
    }

    public function test_messages_threaded_correctly(): void
    {
        $this->actingAs($this->buyer)->post(route('wholesale.rfq.store', $this->product), ['quantity' => 20]);
        $rfq = WholesaleRfq::first();

        $this->actingAs($this->buyer)->post(route('wholesale.rfq.message', $rfq), ['message' => 'Hello']);
        $this->actingAs($this->providerUser)->post(route('provider.wholesale-rfq.message', $rfq), ['message' => 'Hi there']);

        $this->assertSame(2, WholesaleRfqMessage::count());
        $this->assertSame('buyer', WholesaleRfqMessage::orderBy('id', 'asc')->first()->sender_role);
        $this->assertSame('provider', WholesaleRfqMessage::orderBy('id', 'desc')->first()->sender_role);
    }

    public function test_rfq_ownership_checks(): void
    {
        $this->actingAs($this->buyer)->post(route('wholesale.rfq.store', $this->product), ['quantity' => 20]);
        $rfq = WholesaleRfq::first();

        $other = User::create(['name' => 'O', 'email' => 'o@t.com', 'password' => 'pass1234', 'role' => 'traveler']);
        $response = $this->actingAs($other)->get(route('wholesale.rfq.show', $rfq));
        $response->assertStatus(403);
    }
}