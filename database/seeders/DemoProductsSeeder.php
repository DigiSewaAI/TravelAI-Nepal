<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Provider;
use App\Models\ShopDetail;
use App\Models\RentalDetail;
use App\Models\WholesaleDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoProductsSeeder extends Seeder
{
    public function run(): void
    {
        // Get demo providers (from DemoProvidersSeeder)
        $hotelProvider = Provider::where('code', 'DMH')->first();
        $activityProvider = Provider::where('code', 'DMA')->first();
        $experienceProvider = Provider::where('code', 'DME')->first();

        // Fallback: any provider with shop-owner/rental-provider/wholesale-provider type
        $shopProvider = Provider::whereHas('types', fn($q) => $q->where('slug', 'shop-owner'))->first()
            ?? $hotelProvider;
        $rentalProvider = Provider::whereHas('types', fn($q) => $q->where('slug', 'rental-provider'))->first()
            ?? $experienceProvider;
        $wholesaleProvider = Provider::whereHas('types', fn($q) => $q->where('slug', 'wholesale-provider'))->first()
            ?? $activityProvider;

        if (!$shopProvider || !$rentalProvider || !$wholesaleProvider) {
            $this->command->warn('⚠️ Demo providers missing — skipping DemoProductsSeeder.');
            return;
        }

        // ═══ SHOP PRODUCTS (3) ═══
        $shopProducts = [
            [
                'name'        => 'Handmade Wooden Murti (Buddha)',
                'description' => 'Hand-carved wooden Buddha statue from Bhaktapur artisans.',
                'price'       => 2500,
                'stock_count' => 15,
                'sku'         => 'MURTI-BUD-001',
            ],
            [
                'name'        => 'Traditional Nepali Dhaka Topi',
                'description' => 'Handwoven Dhaka fabric topi — authentic Nepali craftsmanship.',
                'price'       => 850,
                'stock_count' => 50,
                'sku'         => 'TOPI-DHK-001',
            ],
            [
                'name'        => 'Handmade Pashmina Shawl',
                'description' => 'Premium Pashmina shawl from Kathmandu workshops.',
                'price'       => 4500,
                'stock_count' => 20,
                'sku'         => 'SHAWL-PSH-001',
            ],
        ];

        foreach ($shopProducts as $data) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'provider_id'  => $shopProvider->id,
                    'product_type' => 'shop',
                    'name'         => $data['name'],
                    'description'  => $data['description'],
                    'price'        => $data['price'],
                    'currency'     => 'NPR',
                    'status'       => 'active',
                ]
            );

            ShopDetail::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'stock_count' => $data['stock_count'],
                    'sku'         => $data['sku'],
                ]
            );
        }

        // ═══ RENTAL PRODUCTS (3) ═══
        $rentalProducts = [
            [
                'name'                 => 'Down Jacket (Winter Trek)',
                'price'                => 500,
                'rental_price_per_day' => 150,
                'rental_deposit'       => 2000,
                'rental_condition'     => 'new',
                'rental_min_days'      => 1,
                'rental_max_days'      => 30,
                'late_fee_per_day'     => 100,
                'damage_deposit_pct'   => 50,
                'lost_deposit_pct'     => 100,
            ],
            [
                'name'                 => 'Sleeping Bag (-20°C Rated)',
                'price'                => 800,
                'rental_price_per_day' => 200,
                'rental_deposit'       => 3000,
                'rental_condition'     => 'good',
                'rental_min_days'      => 1,
                'rental_max_days'      => 30,
                'late_fee_per_day'     => 150,
                'damage_deposit_pct'   => 40,
                'lost_deposit_pct'     => 100,
            ],
            [
                'name'                 => 'Trekking Poles (Pair)',
                'price'                => 300,
                'rental_price_per_day' => 80,
                'rental_deposit'       => 1000,
                'rental_condition'     => 'new',
                'rental_min_days'      => 1,
                'rental_max_days'      => 30,
                'late_fee_per_day'     => 50,
                'damage_deposit_pct'   => 30,
                'lost_deposit_pct'     => 100,
            ],
        ];

        foreach ($rentalProducts as $data) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'provider_id'  => $rentalProvider->id,
                    'product_type' => 'rental',
                    'name'         => $data['name'],
                    'description'  => 'Quality rental gear for trekking adventures.',
                    'price'        => $data['price'],
                    'currency'     => 'NPR',
                    'status'       => 'active',
                ]
            );

            RentalDetail::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'rental_price_per_day' => $data['rental_price_per_day'],
                    'rental_deposit'       => $data['rental_deposit'],
                    'rental_condition'     => $data['rental_condition'],
                    'rental_min_days'      => $data['rental_min_days'],
                    'rental_max_days'      => $data['rental_max_days'],
                    'late_fee_per_day'     => $data['late_fee_per_day'],
                    'damage_deposit_pct'   => $data['damage_deposit_pct'],
                    'lost_deposit_pct'     => $data['lost_deposit_pct'],
                ]
            );
        }

        // ═══ WHOLESALE PRODUCTS (2) ═══
        $wholesaleProducts = [
            [
                'name'          => 'Bulk Handicraft Bundle (50 units)',
                'price'         => 50000,
                'min_order_qty' => 10,
                'bulk_pricing'  => [
                    ['min_qty' => 10, 'price' => 5000, 'currency' => 'NPR'],
                    ['min_qty' => 50, 'price' => 4500, 'currency' => 'NPR'],
                    ['min_qty' => 100, 'price' => 4000, 'currency' => 'NPR'],
                ],
            ],
            [
                'name'          => 'Nepali Tea (Ilam) — Wholesale',
                'price'         => 15000,
                'min_order_qty' => 20,
                'bulk_pricing'  => [
                    ['min_qty' => 20, 'price' => 750, 'currency' => 'NPR'],
                    ['min_qty' => 50, 'price' => 700, 'currency' => 'NPR'],
                    ['min_qty' => 100, 'price' => 650, 'currency' => 'NPR'],
                ],
            ],
        ];

        foreach ($wholesaleProducts as $data) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'provider_id'  => $wholesaleProvider->id,
                    'product_type' => 'wholesale',
                    'name'         => $data['name'],
                    'description'  => 'Wholesale bulk products for retailers and exporters.',
                    'price'        => $data['price'],
                    'currency'     => 'NPR',
                    'status'       => 'active',
                ]
            );

            WholesaleDetail::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'min_order_qty' => $data['min_order_qty'],
                    'bulk_pricing'  => $data['bulk_pricing'],
                ]
            );
        }

        $this->command->info('✅ DemoProductsSeeder: 8 products (3 shop + 3 rental + 2 wholesale)');
    }
}