<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\TransportBooking;
use App\Models\TransportVehicle;
use Illuminate\Database\Seeder;

class TransportSeeder extends Seeder
{
    public function run(): void
    {
        // सबै ट्रान्सपोर्ट प्रोभाइडरहरू लिने
        $providers = Provider::whereHas('types', fn($q) => $q->where('slug', 'transport-provider'))->get();
        
        if ($providers->isEmpty()) { 
            $this->command->warn('No transport providers found.'); 
            return; 
        }

        $vehiclesData = [
            ['BA-1-KHA-1234', 'Bus', 42, 'Ram B.', 'KTM → Pokhara', 'on-route'],
            ['BA-2-KHA-5678', 'Deluxe Bus', 36, 'Hari K.', 'KTM → Chitwan', 'available'],
            ['BA-3-CHA-9012', 'Van', 14, 'Sita G.', 'KTM → Nagarkot', 'on-route'],
            ['BA-4-CHA-3456', 'Car', 4, 'Bikash T.', 'Airport Pickup', 'available'],
            ['BA-5-CHA-7890', 'SUV', 7, 'Dipesh R.', 'KTM → Lumbini', 'maintenance'],
            ['BA-6-KHA-2345', 'Hiace', 18, 'Anita K.', 'KTM → Dhulikhel', 'charging'],
        ];

        $passengers = [
            ['Rohan Kapoor', 'rohan.k@gmail.com'], ['Emily Watson', 'emily.w@yahoo.com'],
            ['Arjun Sharma', 'arjun.s@gmail.com'], ['Maria Garcia', 'maria.g@hotmail.com'],
            ['Daniel Lee', 'dan.lee@gmail.com'], ['Priya Nair', 'priya.n@gmail.com']
        ];

        foreach ($providers as $provider) {
            // 1. Create Vehicles for each provider
            $vehicles = [];
            foreach ($vehiclesData as $v) {
                $vehicles[] = TransportVehicle::updateOrCreate(
                    ['provider_id' => $provider->id, 'vehicle_number' => $v[0]],
                    ['vehicle_type' => $v[1], 'capacity' => $v[2], 'driver_name' => $v[3], 'route_name' => $v[4], 'status' => $v[5]]
                );
            }

            // 2. Create Bookings for each provider
            foreach ($passengers as $i => $p) {
                $veh = $vehicles[array_rand($vehicles)];
                TransportBooking::updateOrCreate(
                    ['provider_id' => $provider->id, 'passenger_email' => $p[1]],
                    [
                        'vehicle_id' => $veh->id,
                        'passenger_name' => $p[0],
                        'booking_ref' => 'TRP-' . $provider->id . '-' . (1000 + $i),
                        'route_name' => $veh->route_name,
                        'travel_date' => now()->addDays(rand(0, 3)),
                        'amount' => rand(800, 3500),
                        'status' => $i % 2 === 0 ? 'confirmed' : 'pending',
                    ]
                );
            }
        }

        $this->command->info("Transport Seeded for " . $providers->count() . " providers.");
    }
}