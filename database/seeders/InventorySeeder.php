<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $providers = Provider::whereHas('types', fn($q) => $q->whereIn('slug', ['shop-owner', 'rental-provider', 'wholesale-provider']))
            ->with('products')
            ->get();

        if ($providers->isEmpty()) {
            $this->command->warn('No product providers found.');
            return;
        }

        $totalUpdated = 0;
        foreach ($providers as $provider) {
            foreach ($provider->products as $product) {
                $sku = 'SKU-' . $provider->id . '-' . str_pad((string) $product->id, 4, '0', STR_PAD_LEFT);

                // Deterministic spread: mix of low, normal, some out-of-stock
                $seed = crc32((string) $product->id) % 10;
                $stock = match(true) {
                    $seed === 0 => 0,               // Out of stock
                    $seed <= 3 => rand(1, 5),       // Low stock
                    default    => rand(10, 60),     // Normal
                };

                $threshold = match(true) {
                    $seed <= 3 => rand(8, 12),
                    default    => 5,
                };

                $costPrice = round((float) $product->price * (rand(60, 75) / 100), 2);

                $product->update([
                    'sku'                 => $sku,
                    'stock_quantity'      => $stock,
                    'low_stock_threshold' => $threshold,
                    'cost_price'          => $costPrice,
                ]);

                $totalUpdated++;
            }
        }

        $this->command->info("Inventory seeded: {$totalUpdated} products across " . $providers->count() . " providers.");
    }
}