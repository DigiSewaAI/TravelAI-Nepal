<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Provider;
use App\Models\ShopDetail;
use App\Models\RentalDetail;
use App\Models\WholesaleDetail;
use Illuminate\Database\Seeder;

class DemoProductsSeeder extends Seeder
{
    public function run(): void
    {
        $shopProvider      = Provider::where('code', 'KHC')->first();
        $rentalProvider    = Provider::where('code', 'HGR')->first();
        $wholesaleProvider = Provider::where('code', 'NWE')->first();

        if (!$shopProvider || !$rentalProvider || !$wholesaleProvider) {
            $this->command->warn('Demo providers missing.');
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
                'cover_image' => 'https://images.unsplash.com/photo-1582653368214-57e2bd5f5d4e?q=80&w=800&auto=format&fit=crop',
            ],
            [
                'slug'        => 'traditional-nepali-dhaka-topi',
                'name'        => 'Traditional Nepali Dhaka Topi',
                'description' => 'Handwoven Dhaka fabric topi - authentic Nepali craftsmanship.',
                'price'       => 850,
                'stock_count' => 50,
                'sku'         => 'TOPI-DHK-001',
                'cover_image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?q=80&w=800&auto=format&fit=crop',
            ],
            [
                'slug'        => 'handmade-pashmina-shawl',
                'name'        => 'Handmade Pashmina Shawl',
                'description' => 'Premium Pashmina shawl from Kathmandu workshops.',
                'price'       => 4500,
                'stock_count' => 20,
                'sku'         => 'SHAWL-PSH-001',
                'cover_image' => 'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?q=80&w=800&auto=format&fit=crop',
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
                    'cover_image'  => $data['cover_image'],
                ]
            );
            ShopDetail::updateOrCreate(
                ['product_id' => $product->id],
                ['stock_count' => $data['stock_count'], 'sku' => $data['sku']]
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
                'cover_image'          => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?q=80&w=800&auto=format&fit=crop', // Jacket
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
                'cover_image'          => 'https://images.unsplash.com/photo-1523987355523-c7b5b0dd90a7?q=80&w=800&auto=format&fit=crop', // Camping
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
                'cover_image'          => 'https://images.unsplash.com/photo-1551632811-561732d1e306?q=80&w=800&auto=format&fit=crop', // Hiking
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
                    'cover_image'  => $data['cover_image'],
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
                'cover_image'   => 'https://images.unsplash.com/photo-1528459801416-a9e53bbf4e17?q=80&w=800&auto=format&fit=crop',
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
                'cover_image'   => 'https://images.unsplash.com/photo-1594631252845-29fc4cc8cde9?q=80&w=800&auto=format&fit=crop',
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
                    'cover_image'  => $data['cover_image'],
                ]
            );
            WholesaleDetail::updateOrCreate(
                ['product_id' => $product->id],
                ['min_order_qty' => $data['min_order_qty'], 'bulk_pricing' => $data['bulk_pricing']]
            );
        }

        $this->command->info('DemoProductsSeeder: 8 products updated with real images.');
    }
}