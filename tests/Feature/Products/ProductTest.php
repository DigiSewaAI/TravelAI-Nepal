<?php

namespace Tests\Feature\Products;

use App\Models\Plan;
use App\Models\Product;
use App\Models\Provider;
use App\Models\RentalDetail;
use App\Models\ShopDetail;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WholesaleDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PATH-3A D9: Product CRUD + limits + polymorphic details.
 *
 * Covers 12 tests:
 *  - Create per type (shop/rental/wholesale)
 *  - Type-specific validation
 *  - Plan limit enforcement (free cap, enterprise unlimited)
 *  - Ownership checks (edit/delete)
 *  - Scopes + polymorphic accessors
 */
class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name'     => 'Test Owner',
            'email'    => 'owner@example.test',
            'password' => 'password123',
            'role'     => 'provider_owner',
        ]);

        $this->provider = Provider::create([
            'user_id'             => $this->owner->id,
            'name'                => 'Test Provider',
            'slug'                => 'test-provider',
            'verification_status' => 'verified',
            'is_active'           => true,
        ]);
    }

    private function attachPlan(string $slug, array $limits): void
    {
        $plan = Plan::create([
            'name'             => ucfirst($slug),
            'slug'             => $slug,
            'description'      => null,
            'price_monthly'    => 0,
            'price_yearly'     => 0,
            'features'         => [],
            'limits'           => $limits,
            'requires_contact' => false,
        ]);

        Subscription::create([
            'provider_id'      => $this->provider->id,
            'plan_id'          => $plan->id,
            'start_date'       => now(),
            'end_date'         => now()->addMonth(),
            'status'           => 'active',
            'billing_interval' => 'monthly',
        ]);
    }

    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'name'        => 'Test Product',
            'description' => 'Test description',
            'price'       => 100,
            'currency'    => 'NPR',
            'status'      => 'active',
        ], $overrides);
    }

    // ─── 1 ───────────────────────────────────────────
    public function test_provider_can_create_shop_product(): void
    {
        $response = $this->actingAs($this->owner)->post(
            route('provider.products.store'),
            $this->basePayload([
                'product_type' => 'shop',
                'name'         => 'Handmade Murti',
                'stock_count'  => 10,
                'sku'          => 'MURTI-001',
            ])
        );

        $response->assertRedirect(route('provider.products.index'));

        $product = Product::where('name', 'Handmade Murti')->first();
        $this->assertNotNull($product);
        $this->assertSame('shop', $product->product_type);
        $this->assertSame($this->provider->id, $product->provider_id);

        $this->assertDatabaseHas('shop_details', [
            'product_id'  => $product->id,
            'stock_count' => 10,
            'sku'         => 'MURTI-001',
        ]);
    }

    // ─── 2 ───────────────────────────────────────────
    public function test_provider_can_create_rental_product(): void
    {
        $response = $this->actingAs($this->owner)->post(
            route('provider.products.store'),
            $this->basePayload([
                'product_type'         => 'rental',
                'name'                 => 'Down Jacket',
                'rental_price_per_day' => 50,
                'rental_deposit'       => 200,
                'rental_condition'     => 'good',
                'rental_min_days'      => 1,
                'rental_max_days'      => 30,
            ])
        );

        $response->assertRedirect(route('provider.products.index'));

        $product = Product::where('name', 'Down Jacket')->first();
        $this->assertNotNull($product);
        $this->assertSame('rental', $product->product_type);

        $this->assertDatabaseHas('rental_details', [
            'product_id'           => $product->id,
            'rental_price_per_day' => 50,
            'rental_deposit'       => 200,
        ]);
    }

    // ─── 3 ───────────────────────────────────────────
    public function test_provider_can_create_wholesale_product(): void
    {
        $response = $this->actingAs($this->owner)->post(
            route('provider.products.store'),
            $this->basePayload([
                'product_type'  => 'wholesale',
                'name'          => 'Bulk Pashmina',
                'min_order_qty' => 50,
            ])
        );

        $response->assertRedirect(route('provider.products.index'));

        $product = Product::where('name', 'Bulk Pashmina')->first();
        $this->assertNotNull($product);
        $this->assertSame('wholesale', $product->product_type);

        $this->assertDatabaseHas('wholesale_details', [
            'product_id'    => $product->id,
            'min_order_qty' => 50,
        ]);
    }

    // ─── 4 ───────────────────────────────────────────
    public function test_rental_product_requires_price_per_day(): void
    {
        $response = $this->actingAs($this->owner)->post(
            route('provider.products.store'),
            $this->basePayload([
                'product_type' => 'rental',
                'name'         => 'Missing Price Rental',
            ])
        );

        $response->assertSessionHasErrors('rental_price_per_day');
        $this->assertNull(Product::where('name', 'Missing Price Rental')->first());
    }

    // ─── 5 ───────────────────────────────────────────
    public function test_wholesale_product_requires_min_order_qty(): void
    {
        $response = $this->actingAs($this->owner)->post(
            route('provider.products.store'),
            $this->basePayload([
                'product_type' => 'wholesale',
                'name'         => 'Missing MOQ Wholesale',
            ])
        );

        $response->assertSessionHasErrors('min_order_qty');
        $this->assertNull(Product::where('name', 'Missing MOQ Wholesale')->first());
    }

    // ─── 6 ───────────────────────────────────────────
    public function test_invalid_product_type_rejected(): void
    {
        $response = $this->actingAs($this->owner)->post(
            route('provider.products.store'),
            $this->basePayload([
                'product_type' => 'invalid',
                'name'         => 'Bad Type',
            ])
        );

        $response->assertSessionHasErrors('product_type');
        $this->assertSame(0, Product::count());
    }

    // ─── 7 ───────────────────────────────────────────
    public function test_free_plan_limit_enforced(): void
    {
        // No subscription → Free default = 5 products
        for ($i = 0; $i < 5; $i++) {
            Product::create([
                'provider_id'  => $this->provider->id,
                'product_type' => 'shop',
                'name'         => "Product {$i}",
                'slug'         => "product-{$i}-" . uniqid(),
                'price'        => 10,
                'currency'     => 'NPR',
                'status'       => 'active',
            ]);
        }

        $this->assertSame(5, Product::where('provider_id', $this->provider->id)->count());

        $response = $this->actingAs($this->owner)->post(
            route('provider.products.store'),
            $this->basePayload([
                'product_type' => 'shop',
                'name'         => 'Product 6',
            ])
        );

        $response->assertSessionHasErrors('limit');
        $this->assertSame(5, Product::where('provider_id', $this->provider->id)->count());
    }

    // ─── 8 ───────────────────────────────────────────
    public function test_enterprise_plan_unlimited(): void
    {
        $this->attachPlan('enterprise', ['max_products' => -1]);

        for ($i = 0; $i < 10; $i++) {
            Product::create([
                'provider_id'  => $this->provider->id,
                'product_type' => 'shop',
                'name'         => "Product {$i}",
                'slug'         => "product-{$i}-" . uniqid(),
                'price'        => 10,
                'currency'     => 'NPR',
                'status'       => 'active',
            ]);
        }

        $response = $this->actingAs($this->owner)->post(
            route('provider.products.store'),
            $this->basePayload([
                'product_type' => 'shop',
                'name'         => 'Product 11',
            ])
        );

        $response->assertRedirect(route('provider.products.index'));
        $this->assertSame(11, Product::where('provider_id', $this->provider->id)->count());
    }

    // ─── 9 ───────────────────────────────────────────
    public function test_provider_cannot_edit_others_product(): void
    {
        $other = User::create([
            'name'     => 'Other',
            'email'    => 'other@example.test',
            'password' => 'password123',
            'role'     => 'provider_owner',
        ]);
        $otherProvider = Provider::create([
            'user_id'             => $other->id,
            'name'                => 'Other Provider',
            'slug'                => 'other-provider',
            'verification_status' => 'verified',
            'is_active'           => true,
        ]);

        $foreign = Product::create([
            'provider_id'  => $otherProvider->id,
            'product_type' => 'shop',
            'name'         => 'Foreign Product',
            'slug'         => 'foreign-product',
            'price'        => 10,
            'currency'     => 'NPR',
            'status'       => 'active',
        ]);

        $response = $this->actingAs($this->owner)->get(
            route('provider.products.edit', $foreign)
        );

        $response->assertStatus(403);
    }

    // ─── 10 ──────────────────────────────────────────
    public function test_provider_cannot_delete_others_product(): void
    {
        $other = User::create([
            'name'     => 'Other2',
            'email'    => 'other2@example.test',
            'password' => 'password123',
            'role'     => 'provider_owner',
        ]);
        $otherProvider = Provider::create([
            'user_id'             => $other->id,
            'name'                => 'Other Provider 2',
            'slug'                => 'other-provider-2',
            'verification_status' => 'verified',
            'is_active'           => true,
        ]);

        $foreign = Product::create([
            'provider_id'  => $otherProvider->id,
            'product_type' => 'shop',
            'name'         => 'Foreign Delete',
            'slug'         => 'foreign-delete',
            'price'        => 10,
            'currency'     => 'NPR',
            'status'       => 'active',
        ]);

        $response = $this->actingAs($this->owner)->delete(
            route('provider.products.destroy', $foreign)
        );

        $response->assertStatus(403);
        $this->assertDatabaseHas('products', ['id' => $foreign->id]);
    }

    // ─── 11 ──────────────────────────────────────────
    public function test_product_scopes_work(): void
    {
        Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'shop', 'name' => 'S1', 'slug' => 's1',
            'price' => 1, 'currency' => 'NPR', 'status' => 'active',
        ]);
        Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'rental', 'name' => 'R1', 'slug' => 'r1',
            'price' => 1, 'currency' => 'NPR', 'status' => 'active',
        ]);
        Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'wholesale', 'name' => 'W1', 'slug' => 'w1',
            'price' => 1, 'currency' => 'NPR', 'status' => 'active',
        ]);
        Product::create([
            'provider_id' => $this->provider->id,
            'product_type' => 'shop', 'name' => 'S2-inactive', 'slug' => 's2',
            'price' => 1, 'currency' => 'NPR', 'status' => 'inactive',
        ]);

        $this->assertSame(2, Product::shop()->count());
        $this->assertSame(1, Product::rental()->count());
        $this->assertSame(1, Product::wholesale()->count());
        $this->assertSame(3, Product::active()->count());
    }

    // ─── 12 ──────────────────────────────────────────
    public function test_polymorphic_details_created_correctly(): void
    {
        $this->actingAs($this->owner)->post(
            route('provider.products.store'),
            $this->basePayload([
                'product_type' => 'shop',
                'name'         => 'PShop',
                'stock_count'  => 5,
            ])
        );
        $this->actingAs($this->owner)->post(
            route('provider.products.store'),
            $this->basePayload([
                'product_type'         => 'rental',
                'name'                 => 'PRent',
                'rental_price_per_day' => 20,
            ])
        );
        $this->actingAs($this->owner)->post(
            route('provider.products.store'),
            $this->basePayload([
                'product_type'  => 'wholesale',
                'name'          => 'PWhole',
                'min_order_qty' => 10,
            ])
        );

        $shop  = Product::where('name', 'PShop')->first();
        $rent  = Product::where('name', 'PRent')->first();
        $whole = Product::where('name', 'PWhole')->first();

        $this->assertInstanceOf(ShopDetail::class, $shop->details());
        $this->assertInstanceOf(RentalDetail::class, $rent->details());
        $this->assertInstanceOf(WholesaleDetail::class, $whole->details());

        $this->assertEquals(5, $shop->shopDetail->stock_count);
        $this->assertEquals(20, (float) $rent->rentalDetail->rental_price_per_day);
        $this->assertEquals(10, $whole->wholesaleDetail->min_order_qty);
    }
}