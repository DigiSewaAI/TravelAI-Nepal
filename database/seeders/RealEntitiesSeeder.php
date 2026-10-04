<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Booking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RealEntitiesSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        $anju = User::updateOrCreate(
            ['email' => 'anjuregmimesh@gmail.com'],
            ['name' => 'Anju Regmi', 'phone' => '9812345678', 'password' => Hash::make('Himalayan@1980'), 'role' => 'provider_owner']
        );
        $pareen = User::updateOrCreate(
            ['email' => 'regmiashish629@gmail.com'],
            ['name' => 'Pareen Regmi', 'phone' => '9841434711', 'password' => Hash::make('Himalayan@1980'), 'role' => 'provider_owner']
        );
        $john = User::updateOrCreate(
            ['email' => 'shresthaxok@gmail.com'],
            ['name' => 'John Adreson', 'phone' => '9812345679', 'password' => Hash::make('Himalayan@1980'), 'role' => 'traveler']
        );

        // Providers
        $hjo = Provider::updateOrCreate(
            ['code' => 'HJO'],
            [
                'user_id' => $anju->id,
                'name' => 'The Himalayan Journey',
                'slug' => 'the-himalayan-journey',
                'description' => 'Expert trekking and tour agency in Nepal. Specializing in Annapurna, Everest, and Langtang treks.',
                'contact_email' => 'bookings@thehimalayanjourney.com',
                'contact_phone' => '+977-1-4567890',
                'address' => 'Thamel, Kathmandu, Nepal',
                'website' => 'https://www.thehimalayanjourney.com',
                'verification_status' => 'verified',
                'is_active' => true,
            ]
        );
        $htp = Provider::updateOrCreate(
            ['code' => 'HTP'],
            [
                'user_id' => $pareen->id,
                'name' => 'The Himalayan Travels Pvt. Ltd',
                'slug' => 'the-himalayan-travels-pvt-ltd-mLNcXQ',
                'description' => 'Trusted transport service provider based in Nepal, specializing in safe, comfortable, and reliable travel solutions across the country.',
                'contact_email' => 'regmiashish629@gmail.com',
                'contact_phone' => '9841434711',
                'address' => 'Boudha, Kathmandu Nepal',
                'verification_status' => 'verified',
                'is_active' => true,
            ]
        );

        // Services (4 public + 1 hidden for booking FK integrity)
        $s27 = Service::updateOrCreate(
            ['provider_id' => $hjo->id, 'slug' => 'annapurna-base-camp-test-8rTh1S'],
            [
                'service_category_id' => 1,
                'name' => 'Annapurna Base Camp Trek',
                'description' => 'Experience the classic Annapurna Base Camp trek — a 14-day journey through Nepal\'s most iconic Himalayan landscapes. Starting from Kathmandu, you\'ll travel to Pokhara and trek through traditional Gurung villages, rhododendron forests, and terraced hillsides before reaching the legendary Annapurna Base Camp.',
                'price' => 1600.00,
                'currency' => 'USD',
                'cover_image' => 'services/kbWOgVUNc3bWeitddd4eDt2OmWiaNJoh3pSGl4NF.jpg',
                'status' => 'active',
                'itinerary_status' => 'published',
            ]
        );
        $s28 = Service::updateOrCreate(
            ['provider_id' => $hjo->id, 'slug' => 'everest-base-camp-trek-14-days'],
            [
                'service_category_id' => 1,
                'name' => 'Everest Base Camp Trek - 14 Days',
                'description' => 'Classic trek to Everest Base Camp via Lukla, Namche, and Gorak Shep.',
                'price' => 1200.00,
                'currency' => 'USD',
                'status' => 'inactive',
                'itinerary_status' => 'published',
            ]
        );
        $s1232 = Service::updateOrCreate(
            ['provider_id' => $hjo->id, 'slug' => 'everest-base-camp-trek-14-days-BNarn9'],
            [
                'service_category_id' => 1,
                'name' => 'Everest Base Camp Trek — 14 Days',
                'description' => 'Experience the ultimate Himalayan adventure to the base of the world\'s highest peak. This 14-day trek takes you through Sherpa villages, ancient monasteries, and dramatic glacial landscapes.',
                'price' => 1450.00,
                'currency' => 'USD',
                'cover_image' => 'services/FxmfPYEUIsIkHrrnETPphhaX0Nu0YNDuLkhaCktT.jpg',
                'status' => 'active',
                'itinerary_status' => 'published',
            ]
        );
        $s1240 = Service::updateOrCreate(
            ['provider_id' => $hjo->id, 'slug' => 'bhutan-cultural-tour-TtvYV2'],
            [
                'service_category_id' => 2,
                'name' => 'Bhutan Cultural Tour',
                'description' => 'Bhutan Cultural Tour – 6 Days / 5 Nights | Departure from Kathmandu. Experience the magic of the Kingdom of Bhutan on this 6-day cultural journey departing from Kathmandu.',
                'price' => 1800.00,
                'currency' => 'USD',
                'cover_image' => 'services/rJEkc6zLEaVrc7SJW390ruPC8j5PCT32aM1cu1Yo.jpg',
                'status' => 'active',
                'itinerary_status' => 'draft',
            ]
        );
        $s1242 = Service::updateOrCreate(
            ['provider_id' => $htp->id, 'slug' => 'kathmandu-to-pokhara-private-car-transfer-GenDY9'],
            [
                'service_category_id' => 5,
                'name' => 'Kathmandu to Pokhara Private Car Transfer',
                'description' => 'Enjoy a smooth 200 km journey from Kathmandu to Pokhara in a well-maintained private car. Includes experienced English-speaking driver, fuel, and tolls.',
                'price' => 600.00,
                'currency' => 'USD',
                'cover_image' => 'services/34rliH6eml114WU1wW1HQNnGV5D1KVpnNFmkhKof.jpg',
                'status' => 'active',
                'itinerary_status' => 'draft',
            ]
        );

        // Bookings (preserving FK chains)
        // SEEDER-QR-CODE-DEFAULT-01: qr_code explicitly set (WithoutModelEvents disables model hooks)
        Booking::updateOrCreate(
            ['booking_number' => 'HJO-BK-26-00001'],
            [
                'traveler_id' => $john->id, 'service_id' => $s27->id,
                'guest_count' => 1, 'booking_date' => '2026-08-12', 'start_date' => '2026-08-17',
                'status' => 'completed', 'quota_month' => '2026-09', 'visibility' => 'public',
                'qr_code' => 'QR-' . strtoupper(\Illuminate\Support\Str::random(12)),
            ]
        );
        Booking::updateOrCreate(
            ['booking_number' => 'HJO-BK-26-00002'],
            [
                'traveler_id' => $john->id, 'service_id' => $s28->id,
                'guest_count' => 1, 'booking_date' => '2026-09-02', 'start_date' => '2026-09-12',
                'status' => 'confirmed', 'quota_month' => '2026-09', 'visibility' => 'private',
                'qr_code' => 'QR-' . strtoupper(\Illuminate\Support\Str::random(12)),
            ]
        );
        Booking::updateOrCreate(
            ['booking_number' => 'HTP-BK-26-00001'],
            [
                'traveler_id' => $john->id, 'service_id' => $s1242->id,
                'guest_count' => 1, 'booking_date' => '2026-09-28', 'start_date' => '2026-09-29',
                'status' => 'confirmed', 'quota_month' => '2026-09', 'visibility' => 'public',
                'qr_code' => 'QR-' . strtoupper(\Illuminate\Support\Str::random(12)),
            ]
        );

        $this->command->info('✅ RealEntitiesSeeder: 3 users, 2 providers, 5 services, 3 bookings');
    }
}