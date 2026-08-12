<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Provider;
use App\Models\ProviderType;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\TrekDetail;
use App\Models\TourDetail;
use App\Models\HotelDetail;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TourismProvidersSeeder extends Seeder
{
    public function run(): void
    {
        // ========== 1. Define Providers with their types ==========
        $providersData = [
            // Trekking Agencies
            [
                'name' => 'Himalayan Guides Nepal',
                'types' => ['trekking-agency'],
                'description' => 'Expert guides for Everest, Annapurna & Langtang. 20+ years experience.',
                'services' => [
                    ['name' => 'Everest Base Camp Trek', 'category' => 'trek', 'price' => 1500, 'duration' => 14, 'difficulty' => 'hard'],
                    ['name' => 'Annapurna Circuit Trek', 'category' => 'trek', 'price' => 1200, 'duration' => 12, 'difficulty' => 'moderate'],
                    ['name' => 'Langtang Valley Trek', 'category' => 'trek', 'price' => 800, 'duration' => 8, 'difficulty' => 'moderate'],
                ]
            ],
            [
    'name' => 'Everest Trailblazers',
    'types' => ['trekking-agency'],
    'description' => 'Premier trekking company for Everest region expeditions.',
    'services' => [
        ['name' => 'Everest Base Camp with Helicopter Return', 'category' => 'trek', 'price' => 2500, 'duration' => 10, 'difficulty' => 'hard'],
        ['name' => 'Gokyo Lakes Trek', 'category' => 'trek', 'price' => 1800, 'duration' => 12, 'difficulty' => 'moderate'],
    ]
],
            [
                'name' => 'Annapurna Eco Treks',
                'types' => ['trekking-agency'],
                'description' => 'Sustainable treks in the Annapurna region with local guides.',
                'services' => [
                    ['name' => 'Annapurna Base Camp Trek', 'category' => 'trek', 'price' => 900, 'duration' => 9, 'difficulty' => 'moderate'],
                    ['name' => 'Mardi Himal Trek', 'category' => 'trek', 'price' => 600, 'duration' => 6, 'difficulty' => 'easy'],
                ]
            ],
            // Tour Agencies
            [
                'name' => 'Nepal Heritage Tours',
                'types' => ['tour-agency'],
                'description' => 'Cultural tours to UNESCO world heritage sites in Nepal.',
                'services' => [
                    ['name' => 'Kathmandu Valley Heritage Tour', 'category' => 'tour', 'price' => 250, 'duration' => 5],
                    ['name' => 'Lumbini & Pokhara Tour', 'category' => 'tour', 'price' => 450, 'duration' => 7],
                ]
            ],
            [
                'name' => 'Mountain Magic Tours',
                'types' => ['tour-agency'],
                'description' => 'Tailored tours combining culture, nature and adventure.',
                'services' => [
                    ['name' => 'Nepal Adventure Tour', 'category' => 'tour', 'price' => 550, 'duration' => 10],
                ]
            ],
            // Hotels
            [
                'name' => 'Hotel Himalayan View',
                'types' => ['hotel'],
                'description' => 'Luxury hotel with stunning mountain views in Pokhara.',
                'services' => [
                    ['name' => 'Deluxe Room with Mountain View', 'category' => 'hotel', 'price' => 120, 'star_rating' => 4],
                    ['name' => 'Suite Room', 'category' => 'hotel', 'price' => 200, 'star_rating' => 4],
                ]
            ],
            [
                'name' => 'Kathmandu Grand Hotel',
                'types' => ['hotel'],
                'description' => 'Heritage hotel in the heart of Kathmandu.',
                'services' => [
                    ['name' => 'Standard Room', 'category' => 'hotel', 'price' => 80, 'star_rating' => 3],
                    ['name' => 'Executive Suite', 'category' => 'hotel', 'price' => 150, 'star_rating' => 3],
                ]
            ],
            [
                'name' => 'Pokhara Lakeside Resort',
                'types' => ['resort'],
                'description' => 'Peaceful resort by Fewa Lake with spa and wellness center.',
                'services' => [
                    ['name' => 'Lake View Room', 'category' => 'hotel', 'price' => 180, 'star_rating' => 5],
                    ['name' => 'Villa with Private Pool', 'category' => 'hotel', 'price' => 350, 'star_rating' => 5],
                ]
            ],
            // Guide
            [
                'name' => 'Professional Guides Nepal',
                'types' => ['guide'],
                'description' => 'Certified and experienced guides for trekking and tours.',
                'services' => [
                    ['name' => 'Private Guide Service', 'category' => 'guide', 'price' => 50],
                    ['name' => 'Group Guide Service', 'category' => 'guide', 'price' => 30],
                ]
            ],
            // Transport
            [
                'name' => 'Himalayan Transport Service',
                'types' => ['transport-provider'],
                'description' => 'Reliable transport for trekkers and tourists across Nepal.',
                'services' => [
                    ['name' => 'Jeep Rental', 'category' => 'transport', 'price' => 120],
                    ['name' => 'Bus Charter', 'category' => 'transport', 'price' => 300],
                ]
            ],
            // Activity
            [
                'name' => 'Adventure Nepal Activities',
                'types' => ['activity-provider'],
                'description' => 'Thrilling activities including paragliding, rafting and bungee.',
                'services' => [
                    ['name' => 'Paragliding in Pokhara', 'category' => 'activity', 'price' => 120],
                    ['name' => 'Trishuli River Rafting', 'category' => 'activity', 'price' => 80],
                ]
            ],
            // Homestay + Local Experience
            [
                'name' => 'Nepal Homestay Experience',
                'types' => ['homestay', 'local-experience'],
                'description' => 'Authentic Nepali homestay with local families.',
                'services' => [
                    ['name' => 'Homestay Experience', 'category' => 'experience', 'price' => 30],
                    ['name' => 'Cooking Class with Family', 'category' => 'experience', 'price' => 20],
                ]
            ],
            // Photographer
            [
                'name' => 'Nepal Photography Studio',
                'types' => ['photographer'],
                'description' => 'Professional photography services for trekkers and travelers.',
                'services' => [
                    ['name' => 'Trekking Photography Package', 'category' => 'experience', 'price' => 200],
                    ['name' => 'Portrait Session', 'category' => 'experience', 'price' => 50],
                ]
            ],
        ];

        // ========== 2. Loop and create with duplicate checks ==========
        foreach ($providersData as $index => $pData) {
            // User
            $email = strtolower(str_replace(' ', '.', $pData['name'])) . '@travelai.com';
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $pData['name'],
                    'email' => $email,
                    'password' => bcrypt('password123'),
                    'role' => 'provider_owner',
                    'phone' => '98' . rand(10000000, 99999999),
                ]);
                $this->command->info("✅ Created user: {$pData['name']}");
            } else {
                $this->command->info("⏩ User already exists: {$pData['name']}, skipping user creation.");
            }

            // Provider
            $provider = Provider::where('name', $pData['name'])->first();
            if (!$provider) {
                $provider = Provider::create([
                    'user_id' => $user->id,
                    'name' => $pData['name'],
                    'slug' => Str::slug($pData['name']) . '-' . Str::random(6),
                    'description' => $pData['description'],
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]);
                $this->command->info("✅ Created provider: {$pData['name']}");
            } else {
                $provider->update([
                    'description' => $pData['description'],
                    'verification_status' => 'verified',
                    'is_active' => true,
                ]);
                $this->command->info("⏩ Provider already exists: {$pData['name']}, updating details.");
            }

            // Assign types
            $typeIds = ProviderType::whereIn('slug', $pData['types'])->pluck('id')->toArray();
            $provider->types()->sync($typeIds);

            // Services
            foreach ($pData['services'] as $sData) {
                $category = ServiceCategory::where('slug', $sData['category'])->first();
                if (!$category) continue;

                $service = Service::where('provider_id', $provider->id)
                    ->where('name', $sData['name'])
                    ->first();

                if (!$service) {
                    $service = Service::create([
                        'provider_id' => $provider->id,
                        'service_category_id' => $category->id,
                        'name' => $sData['name'],
                        'slug' => Str::slug($sData['name']) . '-' . Str::random(6),
                        'description' => $sData['name'] . ' offered by ' . $pData['name'],
                        'price' => $sData['price'],
                        'currency' => 'NPR',
                        'status' => 'active',
                    ]);
                    $this->command->info("   ✅ Created service: {$sData['name']}");
                } else {
                    $this->command->info("   ⏩ Service already exists: {$sData['name']}, skipping.");
                    continue; // Skip creating details and booking for existing service
                }

                // Create detail based on category
                if ($sData['category'] === 'trek') {
                    TrekDetail::create([
                        'service_id' => $service->id,
                        'duration_days' => $sData['duration'] ?? 7,
                        'difficulty' => $sData['difficulty'] ?? 'moderate',
                        'max_altitude' => rand(3000, 5500),
                        'season' => 'Spring/Autumn',
                        'itinerary' => ['Day 1: Arrival', 'Day 2: Trek start', 'Day 3-7: Trekking'],
                    ]);
                } elseif ($sData['category'] === 'tour') {
                    TourDetail::create([
                        'service_id' => $service->id,
                        'duration_days' => $sData['duration'] ?? 5,
                        'inclusions' => ['Hotel', 'Guide', 'Transport'],
                        'exclusions' => ['Lunch', 'Dinner'],
                    ]);
                } elseif ($sData['category'] === 'hotel') {
                    HotelDetail::create([
                        'service_id' => $service->id,
                        'star_rating' => $sData['star_rating'] ?? 3,
                        'amenities' => ['WiFi', 'Room Service', 'Parking'],
                        'check_in_time' => '14:00',
                        'check_out_time' => '12:00',
                    ]);
                }

                // Create a booking for this service
                $booking = Booking::create([
                    'traveler_id' => $user->id,
                    'service_id' => $service->id,
                    'booking_date' => now(),
                    'start_date' => now()->addDays(rand(5, 30)),
                    'status' => 'completed',
                    'qr_code' => Str::random(40),
                ]);

                // ========== 🔥 Add only ONE review per booking ==========
                // Determine rating based on index (but ensure we don't create duplicate)
                $rating = 0;
                $comment = '';
                if ($index % 2 == 0) {
                    $rating = rand(4, 5);
                    $comment = 'Great experience! Highly recommended.';
                } elseif ($index % 3 == 0) {
                    $rating = rand(3, 4);
                    $comment = 'Good service, but could improve a bit.';
                }
                if ($rating > 0) {
                    // Check if review already exists for this booking and user
                    $existingReview = Review::where('booking_id', $booking->id)
                                            ->where('user_id', $user->id)
                                            ->first();
                    if (!$existingReview) {
                        Review::create([
                            'booking_id' => $booking->id,
                            'user_id' => $user->id,
                            'service_id' => $service->id,
                            'rating' => $rating,
                            'comment' => $comment,
                            'status' => 'approved',
                        ]);
                        $this->command->info("      ✅ Added review for service: {$sData['name']}");
                    } else {
                        $this->command->info("      ⏩ Review already exists for this booking, skipping.");
                    }
                }
            }
        }

        $this->command->info("🎉 Seeding completed successfully!");
    }
}