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
use App\Models\QrScan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TourismProvidersSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================
        // STEP 1: CLEANUP DUMMY DATA
        // =====================================================
        $this->command->info('🧹 Cleaning up dummy data...');

        // Delete services with "Dummy" in name
        $deletedServices = Service::where('name', 'like', '%Dummy%')->delete();
        $this->command->info("   ✅ Deleted {$deletedServices} dummy services");

        // Delete bookings that reference dummy services (if any left)
        $deletedBookings = Booking::whereDoesntHave('service')->delete();
        $this->command->info("   ✅ Deleted {$deletedBookings} orphan bookings");

        // Update Guest travelers to "Traveler" (optional)
        $updatedUsers = User::where('name', 'like', 'Guest%')->update(['name' => 'Traveler']);
        $this->command->info("   ✅ Updated {$updatedUsers} guest users to 'Traveler'");

        // Delete orphan QR scans
        $deletedScans = QrScan::whereDoesntHave('booking')->delete();
        $this->command->info("   ✅ Deleted {$deletedScans} orphan QR scans");

        $this->command->info('✅ Cleanup complete. Now seeding realistic data...');

        // =====================================================
        // STEP 2: DEFINE REALISTIC PROVIDERS & SERVICES
        // =====================================================
        $providersData = [
            // Trekking Agencies
            [
                'name' => 'Himalayan Guides Nepal',
                'types' => ['trekking-agency'],
                'description' => 'Expert guides for Everest, Annapurna & Langtang. 20+ years experience.',
                'services' => [
                    ['name' => 'Everest Base Camp Trek', 'category' => 'trek', 'price' => 1250, 'currency' => 'USD', 'duration' => 14, 'difficulty' => 'hard'],
                    ['name' => 'Annapurna Circuit Trek', 'category' => 'trek', 'price' => 1000, 'currency' => 'USD', 'duration' => 12, 'difficulty' => 'moderate'],
                    ['name' => 'Langtang Valley Trek', 'category' => 'trek', 'price' => 700, 'currency' => 'USD', 'duration' => 8, 'difficulty' => 'moderate'],
                ]
            ],
            [
                'name' => 'Everest Trailblazers',
                'types' => ['trekking-agency'],
                'description' => 'Premier trekking company for Everest region expeditions.',
                'services' => [
                    ['name' => 'Everest Base Camp with Helicopter Return', 'category' => 'trek', 'price' => 2500, 'currency' => 'USD', 'duration' => 10, 'difficulty' => 'hard'],
                    ['name' => 'Gokyo Lakes Trek', 'category' => 'trek', 'price' => 1500, 'currency' => 'USD', 'duration' => 12, 'difficulty' => 'moderate'],
                ]
            ],
            [
                'name' => 'Annapurna Eco Treks',
                'types' => ['trekking-agency'],
                'description' => 'Sustainable treks in the Annapurna region with local guides.',
                'services' => [
                    ['name' => 'Annapurna Base Camp Trek', 'category' => 'trek', 'price' => 750, 'currency' => 'USD', 'duration' => 9, 'difficulty' => 'moderate'],
                    ['name' => 'Mardi Himal Trek', 'category' => 'trek', 'price' => 450, 'currency' => 'USD', 'duration' => 6, 'difficulty' => 'easy'],
                ]
            ],
            // Tour Agencies
            [
                'name' => 'Nepal Heritage Tours',
                'types' => ['tour-agency'],
                'description' => 'Cultural tours to UNESCO world heritage sites in Nepal.',
                'services' => [
                    ['name' => 'Kathmandu Valley Heritage Tour', 'category' => 'tour', 'price' => 150, 'currency' => 'USD', 'duration' => 5],
                    ['name' => 'Lumbini & Pokhara Tour', 'category' => 'tour', 'price' => 300, 'currency' => 'USD', 'duration' => 7],
                ]
            ],
            [
                'name' => 'Mountain Magic Tours',
                'types' => ['tour-agency'],
                'description' => 'Tailored tours combining culture, nature and adventure.',
                'services' => [
                    ['name' => 'Nepal Adventure Tour', 'category' => 'tour', 'price' => 350, 'currency' => 'USD', 'duration' => 10],
                ]
            ],
            // Hotels
            [
                'name' => 'Hotel Himalayan View',
                'types' => ['hotel'],
                'description' => 'Luxury hotel with stunning mountain views in Pokhara.',
                'services' => [
                    ['name' => 'Deluxe Room with Mountain View', 'category' => 'hotel', 'price' => 85, 'currency' => 'USD', 'star_rating' => 4],
                    ['name' => 'Suite Room', 'category' => 'hotel', 'price' => 150, 'currency' => 'USD', 'star_rating' => 4],
                ]
            ],
            [
                'name' => 'Kathmandu Grand Hotel',
                'types' => ['hotel'],
                'description' => 'Heritage hotel in the heart of Kathmandu.',
                'services' => [
                    ['name' => 'Standard Room', 'category' => 'hotel', 'price' => 50, 'currency' => 'USD', 'star_rating' => 3],
                    ['name' => 'Executive Suite', 'category' => 'hotel', 'price' => 120, 'currency' => 'USD', 'star_rating' => 3],
                ]
            ],
            [
                'name' => 'Pokhara Lakeside Resort',
                'types' => ['resort'],
                'description' => 'Peaceful resort by Fewa Lake with spa and wellness center.',
                'services' => [
                    ['name' => 'Lake View Room', 'category' => 'hotel', 'price' => 120, 'currency' => 'USD', 'star_rating' => 5],
                    ['name' => 'Villa with Private Pool', 'category' => 'hotel', 'price' => 250, 'currency' => 'USD', 'star_rating' => 5],
                ]
            ],
            // Guide
            [
                'name' => 'Professional Guides Nepal',
                'types' => ['guide'],
                'description' => 'Certified and experienced guides for trekking and tours.',
                'services' => [
                    ['name' => 'Private Guide Service', 'category' => 'guide', 'price' => 40, 'currency' => 'USD'],
                    ['name' => 'Group Guide Service', 'category' => 'guide', 'price' => 25, 'currency' => 'USD'],
                ]
            ],
            // Transport
            [
                'name' => 'Himalayan Transport Service',
                'types' => ['transport-provider'],
                'description' => 'Reliable transport for trekkers and tourists across Nepal.',
                'services' => [
                    ['name' => 'Jeep Rental', 'category' => 'transport', 'price' => 90, 'currency' => 'USD'],
                    ['name' => 'Bus Charter', 'category' => 'transport', 'price' => 250, 'currency' => 'USD'],
                ]
            ],
            // Activity
            [
                'name' => 'Adventure Nepal Activities',
                'types' => ['activity-provider'],
                'description' => 'Thrilling activities including paragliding, rafting and bungee.',
                'services' => [
                    ['name' => 'Paragliding in Pokhara', 'category' => 'activity', 'price' => 120, 'currency' => 'USD'],
                    ['name' => 'Trishuli River Rafting', 'category' => 'activity', 'price' => 70, 'currency' => 'USD'],
                ]
            ],
            // Homestay + Local Experience
            [
                'name' => 'Nepal Homestay Experience',
                'types' => ['homestay', 'local-experience'],
                'description' => 'Authentic Nepali homestay with local families.',
                'services' => [
                    ['name' => 'Homestay Experience', 'category' => 'experience', 'price' => 25, 'currency' => 'USD'],
                    ['name' => 'Cooking Class with Family', 'category' => 'experience', 'price' => 15, 'currency' => 'USD'],
                ]
            ],
            // Photographer
            [
                'name' => 'Nepal Photography Studio',
                'types' => ['photographer'],
                'description' => 'Professional photography services for trekkers and travelers.',
                'services' => [
                    ['name' => 'Trekking Photography Package', 'category' => 'experience', 'price' => 180, 'currency' => 'USD'],
                    ['name' => 'Portrait Session', 'category' => 'experience', 'price' => 40, 'currency' => 'USD'],
                ]
            ],
        ];

        // =====================================================
        // STEP 3: LOOP TO CREATE/UPDATE
        // =====================================================
        foreach ($providersData as $index => $pData) {
            // User
            $email = strtolower(str_replace(' ', '.', $pData['name'])) . '@travelai.com';
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $pData['name'],
                    'email' => $email,
                    'password' => bcrypt('Himalayan@1980'),
                    'role' => 'provider_owner',
                    'phone' => '98' . rand(10000000, 99999999),
                ]);
                $this->command->info("✅ Created user: {$pData['name']}");
            } else {
                $this->command->info("⏩ User already exists: {$pData['name']}");
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
                $this->command->info("⏩ Updated provider: {$pData['name']}");
            }

            // Assign types
            $typeIds = ProviderType::whereIn('slug', $pData['types'])->pluck('id')->toArray();
            $provider->types()->sync($typeIds);

            // Services
            foreach ($pData['services'] as $sData) {
                $category = ServiceCategory::where('slug', $sData['category'])->first();
                if (!$category) continue;

                $currency = $sData['currency'] ?? 'USD';

                $service = Service::updateOrCreate(
                    [
                        'provider_id' => $provider->id,
                        'name' => $sData['name'],
                    ],
                    [
                        'service_category_id' => $category->id,
                        'slug' => Str::slug($sData['name']) . '-' . Str::random(6),
                        'description' => $sData['name'] . ' offered by ' . $pData['name'],
                        'price' => $sData['price'],
                        'currency' => $currency,
                        'status' => 'active',
                    ]
                );
                $this->command->info("   ✅ Service: {$sData['name']} ({$currency} " . number_format($sData['price'], 0) . ")");

                // Create detail based on category
                if ($sData['category'] === 'trek') {
                    TrekDetail::updateOrCreate(
                        ['service_id' => $service->id],
                        [
                            'duration_days' => $sData['duration'] ?? 7,
                            'difficulty' => $sData['difficulty'] ?? 'moderate',
                            'max_altitude' => rand(3000, 5500),
                            'season' => 'Spring/Autumn',
                            'itinerary' => ['Day 1: Arrival', 'Day 2: Trek start', 'Day 3-7: Trekking'],
                        ]
                    );
                } elseif ($sData['category'] === 'tour') {
                    TourDetail::updateOrCreate(
                        ['service_id' => $service->id],
                        [
                            'duration_days' => $sData['duration'] ?? 5,
                            'inclusions' => ['Hotel', 'Guide', 'Transport'],
                            'exclusions' => ['Lunch', 'Dinner'],
                        ]
                    );
                } elseif ($sData['category'] === 'hotel') {
                    HotelDetail::updateOrCreate(
                        ['service_id' => $service->id],
                        [
                            'star_rating' => $sData['star_rating'] ?? 3,
                            'amenities' => ['WiFi', 'Room Service', 'Parking'],
                            'check_in_time' => '14:00',
                            'check_out_time' => '12:00',
                        ]
                    );
                }

                // Create a booking (realistic demo)
                $booking = Booking::firstOrCreate(
                    [
                        'traveler_id' => $user->id,
                        'service_id' => $service->id,
                    ],
                    [
                        'booking_date' => now()->subDays(rand(5, 30)),
                        'start_date' => now()->subDays(rand(1, 10)),
                        'status' => 'completed',
                        'qr_code' => Str::random(40),
                    ]
                );

                // Add review if not exists
                $rating = 0;
                $comment = '';
                if ($index % 2 == 0) {
                    $rating = rand(4, 5);
                    $comment = 'Amazing experience! The guides were professional and the views were breathtaking.';
                } elseif ($index % 3 == 0) {
                    $rating = rand(3, 4);
                    $comment = 'Good value for money. Some logistics could be improved, but overall enjoyable.';
                } else {
                    $rating = rand(4, 5);
                    $comment = 'Perfect trip! Everything was well-organized and exceeded expectations.';
                }
                Review::firstOrCreate(
                    [
                        'booking_id' => $booking->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'service_id' => $service->id,
                        'rating' => $rating,
                        'comment' => $comment,
                        'status' => 'approved',
                    ]
                );
            }
        }

        $this->command->info('🎉 Seeding completed successfully! All dummy data replaced with realistic data.');
    }
}