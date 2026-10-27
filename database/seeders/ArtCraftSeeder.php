<?php

namespace Database\Seeders;

use App\Models\Artwork;
use App\Models\CustomOrder;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class ArtCraftSeeder extends Seeder
{
    public function run(): void
    {
        $providers = Provider::whereHas('types', fn($q) => $q->where('slug', 'art-craft'))->get();
        if ($providers->isEmpty()) { $this->command->warn('No art/craft providers found.'); return; }

        $artworksData = [
            ['Himalayan Dawn', 'Anaya Shakya', 'painting', 45000, 'available', '🖼️', 'linear-gradient(135deg,#fbe7d4,#e88a5a)'],
            ['Sunset Over Kathmandu', 'Anaya Shakya', 'painting', 62000, 'available', '🌅', 'linear-gradient(135deg,#fbd7b8,#d67b52)'],
            ['Bronze Buddha', 'Rabin Maharjan', 'sculpture', 85000, 'sold', '🗿', 'linear-gradient(135deg,#f4e4c9,#b8935c)'],
            ['Handwoven Pashmina', 'Sita Gurung', 'handicraft', 12800, 'available', '🧣', 'linear-gradient(135deg,#f5dfe6,#b05f7e)'],
            ['Forest Silence', 'Anaya Shakya', 'painting', 38000, 'custom', '🌲', 'linear-gradient(135deg,#dfeadd,#6b8e5a)'],
            ['Wooden Ganesh', 'Rabin Maharjan', 'sculpture', 42000, 'available', '🪵', 'linear-gradient(135deg,#efdcc4,#a87748)'],
            ['Ceramic Tea Set', 'Priya Nair', 'handicraft', 8600, 'sold', '🫖', 'linear-gradient(135deg,#f4e5d4,#c7a06d)'],
            ['Monsoon Sky', 'Anaya Shakya', 'painting', 52000, 'available', '🌧️', 'linear-gradient(135deg,#e6dfec,#8a72a5)'],
        ];

        $ordersData = [
            ['Rohan Kapoor', 'rohan.k@gmail.com', 'Painting', 55000, 'pending', 10],
            ['Emily Watson', 'emily.w@yahoo.com', 'Sculpture', 120000, 'progress', 15],
            ['Arjun Sharma', 'arjun.s@gmail.com', 'Handicraft', 18500, 'done', 5],
            ['Maria Garcia', 'maria.g@hotmail.com', 'Mural', 95000, 'progress', 20],
        ];

        foreach ($providers as $provider) {
            foreach ($artworksData as $a) {
                Artwork::updateOrCreate(
                    ['provider_id' => $provider->id, 'name' => $a[0]],
                    ['artist_name' => $a[1], 'category' => $a[2], 'price' => $a[3], 'status' => $a[4], 'emoji' => $a[5], 'gradient' => $a[6]]
                );
            }
            foreach ($ordersData as $o) {
                CustomOrder::updateOrCreate(
                    ['provider_id' => $provider->id, 'client_email' => $o[1]],
                    ['client_name' => $o[0], 'type' => $o[2], 'budget' => $o[3], 'status' => $o[4], 'deadline' => now()->addDays($o[5])]
                );
            }
        }

        $this->command->info("Art/Craft Seeded for " . $providers->count() . " providers.");
    }
}