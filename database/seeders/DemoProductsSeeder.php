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
        // Get dedicated providers (created by DemoProvidersSeeder / ProductionFixSeeder)
        $shopProvider      = Provider::where('code', 'KHC')->first();
        $rentalProvider    = Provider::where('code', 'HGR')->first();
        $wholesaleProvider = Provider::where('code', 'NWE')->first();

        if (!$shopProvider || !$rentalProvider || !$wholesaleProvider) {
            $this->command->warn('Demo providers missing - skipping DemoProductsSeeder.');
            return;
        }

        // === SHOP PRODUCTS (3) ===
        $shopProducts = [
            [
                'slug'        => 'handmade-wooden-murti-buddha',
                'name'        => 'Handmade Wooden Murti (Buddha)',
                'description' => 'Hand-carved wooden Buddha statue from Bhaktapur artisans.',
                'price'       => 2500,
                'stock_count' => 15,
                'sku'         => 'MURTI-BUD-001',
            ],
            [
                'slug'        => 'traditional-nepali-dhaka-topi',
                'name'        => 'Traditional Nepali Dhaka Topi',
                'description' => 'Handwoven Dhaka fabric topi - authentic Nepali craftsmanship.',
                'price'       => 850,
                'stock_count' => 50,
                'sku'         => 'TOPI-DHK-001',
            ],
            [
                'slug'        => 'handmade-pashmina-shawl',
                'name'        => 'Handmade Pashmina Shawl',
                'description' => 'Premium Pashmina shawl from Kathmandu workshops.',
                'price'       => 4500,
                'stock_count' => 20,
                'sku'         => 'SHAWL-PSH-001',
            ],
        ];

        foreach ($shopProducts as $data) {
            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
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

        // === RENTAL PRODUCTS (3) ===
        $rentalProducts = [
            [
                'slug'                 => 'down-jacket-winter-trek',
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
                'slug'                 => 'sleeping-bag-minus-20c-rated',
                'name'                 => 'Sleeping Bag (-20C Rated)',
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
                'slug'                 => 'trekking-poles-pair',
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
            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
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

        // === WHOLESALE PRODUCTS (2) ===
        $wholesaleProducts = [
            [
                'slug'          => 'bulk-handicraft-bundle-50-units',
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
                'slug'          => 'nepali-tea-ilam-wholesale',
                'name'          => 'Nepali Tea (Ilam) - Wholesale',
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
            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
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

        $this->command->info('DemoProductsSeeder: 8 products re-attached to KHC/HGR/NWE');
    }
}