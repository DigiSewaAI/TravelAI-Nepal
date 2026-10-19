<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementsSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            // === FIRST STEPS ===
            ['slug' => 'first_checkin', 'name' => 'First Check-In', 'description' => 'Recorded your first checkpoint scan', 'category' => 'milestone', 'icon' => 'fa-flag', 'rarity' => 'common', 'points' => 10],
            ['slug' => 'first_trek', 'name' => 'First Trek', 'description' => 'Booked your first trek', 'category' => 'milestone', 'icon' => 'fa-hiking', 'rarity' => 'common', 'points' => 20],

            // === STAMP COLLECTORS ===
            ['slug' => 'stamp_collector_5', 'name' => 'Stamp Collector', 'description' => 'Collected 5 unique stamps', 'category' => 'exploration', 'icon' => 'fa-stamp', 'rarity' => 'common', 'points' => 30],
            ['slug' => 'stamp_collector_10', 'name' => 'Globetrotter', 'description' => 'Collected 10 unique stamps', 'category' => 'exploration', 'icon' => 'fa-globe', 'rarity' => 'rare', 'points' => 50],
            ['slug' => 'stamp_collector_25', 'name' => 'Trailblazer', 'description' => 'Collected 25 unique stamps', 'category' => 'exploration', 'icon' => 'fa-fire', 'rarity' => 'epic', 'points' => 75],
            ['slug' => 'stamp_collector_50', 'name' => 'Legendary Explorer', 'description' => 'Collected 50 unique stamps', 'category' => 'exploration', 'icon' => 'fa-crown', 'rarity' => 'legendary', 'points' => 100],

            // === ALTITUDE ===
            ['slug' => 'altitude_3000', 'name' => 'Above the Clouds', 'description' => 'Reached 3,000m altitude', 'category' => 'altitude', 'icon' => 'fa-mountain', 'rarity' => 'common', 'points' => 40],
            ['slug' => 'altitude_4000', 'name' => 'High Altitude Trekker', 'description' => 'Reached 4,000m altitude', 'category' => 'altitude', 'icon' => 'fa-mountain-sun', 'rarity' => 'rare', 'points' => 60],
            ['slug' => 'altitude_5000', 'name' => 'Himalayan Veteran', 'description' => 'Reached 5,000m altitude', 'category' => 'altitude', 'icon' => 'fa-snowflake', 'rarity' => 'epic', 'points' => 80],
            ['slug' => 'altitude_6000', 'name' => 'Summit Master', 'description' => 'Reached 6,000m altitude', 'category' => 'altitude', 'icon' => 'fa-star', 'rarity' => 'legendary', 'points' => 120],

            // === TREK COMPLETIONS ===
            ['slug' => 'trek_completed_3', 'name' => 'Triple Crown', 'description' => 'Completed 3 treks', 'category' => 'achievement', 'icon' => 'fa-medal', 'rarity' => 'rare', 'points' => 50],
            ['slug' => 'trek_completed_5', 'name' => 'Seasoned Trekker', 'description' => 'Completed 5 treks', 'category' => 'achievement', 'icon' => 'fa-trophy', 'rarity' => 'epic', 'points' => 80],
            ['slug' => 'trek_completed_10', 'name' => 'Ultimate Trekker', 'description' => 'Completed 10 treks', 'category' => 'achievement', 'icon' => 'fa-award', 'rarity' => 'legendary', 'points' => 120],

            // === DESTINATION BADGES ===
            ['slug' => 'everest_base_camp', 'name' => 'Everest Base Camp', 'description' => 'Reached Everest Base Camp', 'category' => 'destination', 'icon' => 'fa-flag-checkered', 'rarity' => 'legendary', 'points' => 100],
            ['slug' => 'annapurna_circuit', 'name' => 'Annapurna Trail', 'description' => 'Trekked the Annapurna region', 'category' => 'destination', 'icon' => 'fa-tree', 'rarity' => 'epic', 'points' => 100],
            ['slug' => 'langtang', 'name' => 'Langtang Explorer', 'description' => 'Explored Langtang Valley', 'category' => 'destination', 'icon' => 'fa-leaf', 'rarity' => 'epic', 'points' => 100],
            ['slug' => 'manaslu', 'name' => 'Manaslu Circuit', 'description' => 'Trekked around Manaslu', 'category' => 'destination', 'icon' => 'fa-circle', 'rarity' => 'epic', 'points' => 100],
            ['slug' => 'kanchenjunga', 'name' => 'Kanchenjunga Pioneer', 'description' => 'Explored Kanchenjunga region', 'category' => 'destination', 'icon' => 'fa-gem', 'rarity' => 'legendary', 'points' => 120],
            ['slug' => 'mardi_himal', 'name' => 'Mardi Himal Trekker', 'description' => 'Completed Mardi Himal trek', 'category' => 'destination', 'icon' => 'fa-hiking', 'rarity' => 'rare', 'points' => 80],
            ['slug' => 'ghorepani_poon_hill', 'name' => 'Poon Hill Sunrise', 'description' => 'Witnessed sunrise from Poon Hill', 'category' => 'destination', 'icon' => 'fa-sun', 'rarity' => 'rare', 'points' => 80],
            ['slug' => 'upper_mustang', 'name' => 'Forbidden Kingdom', 'description' => 'Trekked Upper Mustang', 'category' => 'destination', 'icon' => 'fa-landmark', 'rarity' => 'legendary', 'points' => 120],
        ];

        foreach ($achievements as $data) {
            Achievement::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['is_active' => true])
            );
        }

        $count = Achievement::count();
        $this->command->info("✅ AchievementsSeeder: {$count} achievements in catalog");
    }
}