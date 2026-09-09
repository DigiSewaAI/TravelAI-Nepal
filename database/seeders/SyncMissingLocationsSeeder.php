<?php

namespace Database\Seeders;

use App\Models\Waypoint;
use App\Models\Location;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SyncMissingLocationsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔍 Syncing missing locations & fixing overnight flags...');

        // 1️⃣ Landmark type → is_overnight_stop = false
        $landmarks = Waypoint::where('type', 'landmark')
            ->where('is_overnight_stop', true)
            ->get();

        $this->command->info("📌 Found {$landmarks->count()} landmarks marked as overnight. Setting to false...");
        foreach ($landmarks as $waypoint) {
            $waypoint->is_overnight_stop = false;
            $waypoint->save();
        }

        // 2️⃣ Habitable (village/neighborhood) waypoints without location_id
        $waypoints = Waypoint::whereNull('location_id')
            ->whereIn('type', ['village', 'neighborhood'])
            ->where('is_overnight_stop', true)
            ->get();

        $total = $waypoints->count();
        $this->command->info("📊 Found {$total} habitable overnight waypoints without location_id.");

        if ($total === 0) {
            $this->command->info('✅ All habitable overnight waypoints already have location_id!');
            // Still we have fixed landmarks
            $this->command->info("✅ Landmarks set to non-overnight: {$landmarks->count()}");
            return;
        }

        // Ensure provider exists (without email column)
        $user = User::firstOrCreate(
            ['email' => 'remote-providers@travelai.com'],
            [
                'name' => 'Remote Treks Provider System',
                'password' => bcrypt('Himalayan@1980'),
                'role' => 'provider_owner',
            ]
        );

        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) {
            $provider = Provider::create([
                'user_id' => $user->id,
                'name' => 'Remote Treks Provider System',
                'slug' => 'remote-treks-provider',
                'verification_status' => 'verified',
                'is_active' => true,
            ]);
            $this->command->info("✅ Created provider: Remote Treks Provider System");
        }

        $hotelCat = ServiceCategory::where('slug', 'hotel')->first();
        if (!$hotelCat) {
            $this->command->error('❌ Hotel category not found. Please run service categories seeder first.');
            return;
        }

        $updated = 0;
        $created = 0;

        foreach ($waypoints as $waypoint) {
            $cityName = $waypoint->name;

            // Check if location exists
            $location = Location::where('city', $cityName)->first();

            if (!$location) {
                // Create location from waypoint data
                $location = Location::create([
                    'country' => 'Nepal',
                    'state' => $this->guessState($cityName),
                    'city' => $cityName,
                    'latitude' => $waypoint->latitude ?? 28.0,
                    'longitude' => $waypoint->longitude ?? 84.0,
                    'is_habitable' => true,
                ]);
                $created++;
                $this->command->info("✅ Created location: {$cityName}");
            }

            // Set location_id to waypoint
            $waypoint->location_id = $location->id;
            $waypoint->save();
            $updated++;

            // Ensure hotel service exists
            $service = Service::where('location_id', $location->id)
                ->where('service_category_id', $hotelCat->id)
                ->first();

            if (!$service) {
                $slug = Str::slug($cityName) . '-mid-range-lodge';
                Service::create([
                    'provider_id' => $provider->id,
                    'service_category_id' => $hotelCat->id,
                    'name' => "{$cityName} Mid-Range Lodge",
                    'slug' => $slug,
                    'description' => "Mid-range lodge at {$cityName}",
                    'price' => 25,
                    'currency' => 'USD',
                    'status' => 'active',
                    'location_id' => $location->id,
                ]);
                $this->command->info("   ✅ Created service for {$cityName}");
            }
        }

        $this->command->newLine();
        $this->command->info("✅ Sync completed!");
        $this->command->info("   📌 Locations created: {$created}");
        $this->command->info("   📌 Waypoints updated: {$updated}");
        $this->command->info("   📌 Landmarks set to non-overnight: {$landmarks->count()}");
    }

    private function guessState(string $city): string
    {
        $map = [
            'Pokhara' => 'Gandaki',
            'Kathmandu' => 'Bagmati',
            'Lukla' => 'Solukhumbu',
            'Namche' => 'Solukhumbu',
            'Manang' => 'Manang',
            'Jomsom' => 'Mustang',
            'Besisahar' => 'Lamjung',
            'Syabrubesi' => 'Rasuwa',
            'Dhunche' => 'Rasuwa',
            'Kyangjin Gompa' => 'Rasuwa',
            'Gosaikunda' => 'Rasuwa',
            'Simikot' => 'Humla',
            'Jumla' => 'Jumla',
            'Rara Lake' => 'Mugu',
            'Darchula' => 'Bajhang',
            'Sitapur' => 'Bajhang',
            'Khalanga' => 'Bajhang',
            'Api Base Camp' => 'Bajhang',
            'Bajhang' => 'Bajhang',
            'Bajura' => 'Bajura',
            'Dhorpatan' => 'Lumbini',
            'Rolwaling' => 'Dolakha',
            'Dolakha' => 'Dolakha',
            'Tilicho Base Camp' => 'Manang',
            'Khangsar' => 'Manang',
            'Ngawal' => 'Manang',
            'Nar Phu' => 'Manang',
            'Phu Village' => 'Manang',
            'Nar Village' => 'Manang',
            'Koto' => 'Manang',
            'Muktinath' => 'Mustang',
            'Marpha' => 'Mustang',
            'Kagbeni' => 'Mustang',
            'Lo Manthang' => 'Mustang',
            'Damodar Kunda' => 'Mustang',
            'Chhusang' => 'Mustang',
            'Chele' => 'Mustang',
            'Ghemi' => 'Mustang',
            'Tsarang' => 'Mustang',
            'Khingar' => 'Mustang',
            'Chhushyang' => 'Mustang',
            'Tangbe' => 'Mustang',
            'Nar Phu' => 'Manang',
            'Tilicho Lake' => 'Manang',
            'Kang La Pass' => 'Manang',
            'Dole' => 'Solukhumbu',
            'Machhermo' => 'Solukhumbu',
            'Gokyo' => 'Solukhumbu',
            'Kande' => 'Gandaki',
            'Bandipur' => 'Gandaki',
            'Dharan' => 'Koshi',
            'Dhankuta' => 'Koshi',
            'Bhedetar' => 'Koshi',
            'Biratnagar' => 'Koshi',
            'Butwal' => 'Lumbini',
            'Siddharthanagar' => 'Lumbini',
            'Surkhet' => 'Karnali',
            'Birendranagar' => 'Karnali',
            'Kalikot' => 'Karnali',
        ];
        return $map[$city] ?? 'Bagmati';
    }
}