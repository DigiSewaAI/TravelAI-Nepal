<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Provider;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\RestaurantTable;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class RestaurantDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $provider = Provider::whereHas('types', fn($q) => $q->where('slug', 'restaurant'))->first();
        if (!$provider) {
            $this->command->warn('No restaurant provider found. Skipping.');
            return;
        }

        $catId = ServiceCategory::where('slug', 'restaurant')->value('id');
        $service = $provider->services()->where('service_category_id', $catId)->first();

        if (!$service) {
            $this->command->warn('Restaurant provider has no restaurant service. Create a service first.');
            return;
        }

        $menuSeed = [
            ['Chicken Momo', 220, 'main', '🥟'],
            ['Dal Bhat Set', 450, 'main', '🍛'],
            ['Chicken Thukpa', 280, 'main', '🍜'],
            ['Chicken Sekuwa', 390, 'main', '🔥'],
            ['Pizza Margherita', 620, 'main', '🍕'],
            ['Veg Chowmein', 240, 'main', '🍝'],
            ['Biryani', 480, 'main', '🍚'],
            ['Coke', 120, 'drink', '🥤'],
        ];

        $menuItems = [];
        foreach ($menuSeed as [$name, $price, $category, $emoji]) {
            $menuItems[] = MenuItem::updateOrCreate(
                ['service_id' => $service->id, 'name' => $name],
                [
                    'provider_id'  => $provider->id,
                    'price'        => $price,
                    'currency'     => $service->currency ?? 'NPR',
                    'category'     => $category,
                    'emoji'        => $emoji,
                    'is_available' => true,
                ]
            );
        }

        $tableConfig = [
            ['T-01', 4], ['T-02', 2], ['T-03', 4], ['T-04', 6],
            ['T-05', 2], ['T-06', 4], ['T-07', 8], ['T-08', 4],
            ['T-09', 2], ['T-10', 4], ['T-11', 6], ['T-12', 2],
            ['T-13', 4], ['T-14', 4],
        ];
        $tables = [];
        $statuses = ['free', 'occupied', 'reserved', 'cleaning'];
        foreach ($tableConfig as $i => [$num, $seats]) {
            $tables[] = RestaurantTable::updateOrCreate(
                ['provider_id' => $provider->id, 'table_number' => $num],
                [
                    'service_id' => $service->id,
                    'seats'      => $seats,
                    'status'     => $statuses[$i % count($statuses)],
                ]
            );
        }

        // Clean previous seed orders (idempotent re-run)
        $seedOrderNumbers = array_map(fn($n) => '10' . str_pad((string) $n, 2, '0', STR_PAD_LEFT), range(1, 15));
        RestaurantOrder::where('provider_id', $provider->id)
            ->whereIn('order_number', $seedOrderNumbers)
            ->delete();   // cascade deletes restaurant_order_items

        $statusPool = ['pending', 'preparing', 'ready', 'served', 'served', 'served'];
        for ($n = 1; $n <= 15; $n++) {
            $status = $statusPool[array_rand($statusPool)];
            $table  = $tables[array_rand($tables)];
            $itemPool = array_slice($menuItems, 0, 4);

            $order = RestaurantOrder::create([
                'order_number'   => '10' . str_pad((string) $n, 2, '0', STR_PAD_LEFT),
                'provider_id'    => $provider->id,
                'service_id'     => $service->id,
                'table_id'       => $table->id,
                'status'         => $status,
                'currency'       => $service->currency ?? 'NPR',
                'payment_status' => in_array($status, ['served']) ? 'paid' : 'unpaid',
                'paid_at'        => in_array($status, ['served']) ? now()->subMinutes(rand(5, 120)) : null,
                'subtotal'       => 0,
                'tax'            => 0,
                'total'          => 0,
            ]);

            $subtotal = 0;
            $pickCount = rand(1, min(3, count($itemPool)));
            $picks = (array) array_rand($itemPool, $pickCount);
            foreach ($picks as $idx) {
                $mi = $itemPool[$idx];
                $qty = rand(1, 3);
                $line = $mi->price * $qty;
                $subtotal += $line;
                RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'menu_item_id'        => $mi->id,
                    'item_name'           => $mi->name,
                    'unit_price'          => $mi->price,
                    'quantity'            => $qty,
                    'line_total'          => $line,
                ]);
            }
            $tax = round($subtotal * 0.13, 2);
            $order->update(['subtotal' => $subtotal, 'tax' => $tax, 'total' => $subtotal + $tax]);
        }

        $this->command->info("Seeded {$provider->name}: " . count($menuItems) . ' menu items, ' . count($tables) . ' tables, 15 orders.');
    }
}