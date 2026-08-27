<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            // Exploration
            [
                'slug' => 'first_checkin',
                'name' => 'First Stamp',
                'description' => 'Completed your first trekking check-in.',
                'category' => 'exploration',
                'icon' => '🎒',
                'rarity' => 'common',
                'points' => 10,
                'criteria' => ['min_checkins' => 1],
            ],
            [
                'slug' => 'first_trek',
                'name' => 'First Trek',
                'description' => 'Completed your first trek booking.',
                'category' => 'exploration',
                'icon' => '🏔️',
                'rarity' => 'common',
                'points' => 15,
                'criteria' => ['min_treks' => 1],
            ],
            [
                'slug' => 'stamp_collector_5',
                'name' => 'Stamp Collector',
                'description' => 'Visited 5 unique waypoints.',
                'category' => 'exploration',
                'icon' => '📮',
                'rarity' => 'common',
                'points' => 10,
                'criteria' => ['min_unique_waypoints' => 5],
            ],
            [
                'slug' => 'stamp_collector_10',
                'name' => 'Explorer',
                'description' => 'Visited 10 unique waypoints.',
                'category' => 'exploration',
                'icon' => '🧭',
                'rarity' => 'rare',
                'points' => 20,
                'criteria' => ['min_unique_waypoints' => 10],
            ],
            [
                'slug' => 'stamp_collector_25',
                'name' => 'Master Explorer',
                'description' => 'Visited 25 unique waypoints.',
                'category' => 'exploration',
                'icon' => '🌟',
                'rarity' => 'epic',
                'points' => 35,
                'criteria' => ['min_unique_waypoints' => 25],
            ],
            [
                'slug' => 'stamp_collector_50',
                'name' => 'Legendary Explorer',
                'description' => 'Visited 50 unique waypoints.',
                'category' => 'exploration',
                'icon' => '👑',
                'rarity' => 'legendary',
                'points' => 50,
                'criteria' => ['min_unique_waypoints' => 50],
            ],

            // Elevation
            [
                'slug' => 'altitude_3000',
                'name' => 'Mountain Explorer',
                'description' => 'Reached an altitude of 3,000m.',
                'category' => 'elevation',
                'icon' => '⛰️',
                'rarity' => 'common',
                'points' => 15,
                'criteria' => ['min_altitude' => 3000],
            ],
            [
                'slug' => 'altitude_4000',
                'name' => 'High Altitude Trekker',
                'description' => 'Reached an altitude of 4,000m.',
                'category' => 'elevation',
                'icon' => '🏔️',
                'rarity' => 'rare',
                'points' => 25,
                'criteria' => ['min_altitude' => 4000],
            ],
            [
                'slug' => 'altitude_5000',
                'name' => 'Himalayan Explorer',
                'description' => 'Reached an altitude of 5,000m.',
                'category' => 'elevation',
                'icon' => '🗻',
                'rarity' => 'epic',
                'points' => 40,
                'criteria' => ['min_altitude' => 5000],
            ],
            [
                'slug' => 'altitude_6000',
                'name' => 'Extreme Altitude',
                'description' => 'Reached an altitude of 6,000m+',
                'category' => 'elevation',
                'icon' => '🔥',
                'rarity' => 'legendary',
                'points' => 60,
                'criteria' => ['min_altitude' => 6000],
            ],

            // Trek Completion
            [
                'slug' => 'trek_completed_3',
                'name' => 'Trek Veteran',
                'description' => 'Completed 3 treks.',
                'category' => 'trek',
                'icon' => '🥉',
                'rarity' => 'rare',
                'points' => 20,
                'criteria' => ['min_completed_treks' => 3],
            ],
            [
                'slug' => 'trek_completed_5',
                'name' => 'Trek Master',
                'description' => 'Completed 5 treks.',
                'category' => 'trek',
                'icon' => '🥈',
                'rarity' => 'epic',
                'points' => 35,
                'criteria' => ['min_completed_treks' => 5],
            ],
            [
                'slug' => 'trek_completed_10',
                'name' => 'Trek Legend',
                'description' => 'Completed 10 treks.',
                'category' => 'trek',
                'icon' => '🥇',
                'rarity' => 'legendary',
                'points' => 50,
                'criteria' => ['min_completed_treks' => 10],
            ],

            // Destination Specific
            [
                'slug' => 'everest_base_camp',
                'name' => 'Everest Base Camp Trekker',
                'description' => 'Reached Everest Base Camp.',
                'category' => 'destination',
                'icon' => '🏔️',
                'rarity' => 'epic',
                'points' => 40,
                'criteria' => ['destination' => 'everest'],
            ],
            [
                'slug' => 'annapurna_circuit',
                'name' => 'Annapurna Circuit Finisher',
                'description' => 'Completed the Annapurna Circuit.',
                'category' => 'destination',
                'icon' => '⛰️',
                'rarity' => 'epic',
                'points' => 40,
                'criteria' => ['destination' => 'annapurna'],
            ],
            [
                'slug' => 'langtang',
                'name' => 'Langtang Valley Explorer',
                'description' => 'Explored Langtang Valley.',
                'category' => 'destination',
                'icon' => '🌿',
                'rarity' => 'rare',
                'points' => 25,
                'criteria' => ['destination' => 'langtang'],
            ],
            [
                'slug' => 'manaslu',
                'name' => 'Manaslu Circuit Finisher',
                'description' => 'Completed the Manaslu Circuit.',
                'category' => 'destination',
                'icon' => '🗻',
                'rarity' => 'epic',
                'points' => 40,
                'criteria' => ['destination' => 'manaslu'],
            ],
            [
                'slug' => 'kanchenjunga',
                'name' => 'Kanchenjunga Explorer',
                'description' => 'Reached Kanchenjunga Base Camp.',
                'category' => 'destination',
                'icon' => '🏔️',
                'rarity' => 'legendary',
                'points' => 50,
                'criteria' => ['destination' => 'kanchenjunga'],
            ],
            [
                'slug' => 'mardi_himal',
                'name' => 'Mardi Himal Trekker',
                'description' => 'Completed Mardi Himal Trek.',
                'category' => 'destination',
                'icon' => '⛰️',
                'rarity' => 'rare',
                'points' => 20,
                'criteria' => ['destination' => 'mardi'],
            ],
            [
                'slug' => 'ghorepani_poon_hill',
                'name' => 'Poon Hill Trekker',
                'description' => 'Reached Poon Hill.',
                'category' => 'destination',
                'icon' => '🌅',
                'rarity' => 'common',
                'points' => 15,
                'criteria' => ['destination' => 'poon hill'],
            ],
            [
                'slug' => 'upper_mustang',
                'name' => 'Upper Mustang Explorer',
                'description' => 'Explored the mystic Upper Mustang.',
                'category' => 'destination',
                'icon' => '🏜️',
                'rarity' => 'legendary',
                'points' => 45,
                'criteria' => ['destination' => 'mustang'],
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(
                ['slug' => $achievement['slug']],
                $achievement
            );
        }

        $this->command->info('✅ Achievements seeded: ' . count($achievements) . ' records.');
    }
}