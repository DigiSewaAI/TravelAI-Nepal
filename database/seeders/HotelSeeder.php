<?php

namespace Database\Seeders;

use App\Models\HotelBooking;
use App\Models\HotelRoom;
use App\Models\HousekeepingTask;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        // सबै होटल, रिसोर्ट, लज, होमस्टे प्रोभाइडरहरू लिने
        $providers = Provider::whereHas('types', fn($q) => $q->whereIn('slug', ['hotel', 'resort', 'lodge', 'homestay']))->get();
        
        if ($providers->isEmpty()) { 
            $this->command->warn('No accommodation providers found.'); 
            return; 
        }

        $statuses = ['available','occupied','reserved','dirty','maintenance'];
        $guests = [
            ['Rohan Kapoor', 'rohan.k@gmail.com'], ['Emily Watson', 'emily.w@yahoo.com'],
            ['Arjun Sharma', 'arjun.s@gmail.com'], ['Maria Garcia', 'maria.g@hotmail.com'],
            ['Daniel Lee', 'dan.lee@gmail.com'], ['Priya Nair', 'priya.n@gmail.com']
        ];

        foreach ($providers as $provider) {
            // 1. Create 21 Rooms per provider
            $rooms = [];
            for ($floor = 1; $floor <= 3; $floor++) {
                for ($i = 1; $i <= 7; $i++) {
                    $roomNum = $floor . str_pad($i, 2, '0', STR_PAD_LEFT);
                    $rooms[] = HotelRoom::updateOrCreate(
                        ['provider_id' => $provider->id, 'room_number' => $roomNum],
                        [
                            'room_type' => $i % 3 === 0 ? 'Suite' : ($i % 2 === 0 ? 'Deluxe' : 'Standard'),
                            'floor' => $floor,
                            'status' => $statuses[array_rand($statuses)],
                        ]
                    );
                }
            }

            // 2. Create Bookings
            foreach ($guests as $i => $g) {
                $room = $rooms[array_rand($rooms)];
                $checkIn = now()->subDays(rand(1, 3));
                $nights = rand(1, 4);
                $checkOut = $checkIn->copy()->addDays($nights);
                
                HotelBooking::updateOrCreate(
                    ['provider_id' => $provider->id, 'guest_email' => $g[1]],
                    [
                        'room_id' => $room->id,
                        'guest_name' => $g[0],
                        'check_in_date' => $checkIn,
                        'check_out_date' => $checkOut,
                        'nights' => $nights,
                        'amount' => $nights * 18000,
                        'payment_status' => $i % 2 === 0 ? 'paid' : 'pending',
                        'status' => $i % 2 === 0 ? 'checked_in' : 'pending',
                    ]
                );
            }

            // 3. Housekeeping Tasks
            foreach (array_slice($rooms, 0, 5) as $i => $room) {
                HousekeepingTask::updateOrCreate(
                    ['provider_id' => $provider->id, 'room_id' => $room->id],
                    [
                        'task_name' => 'Clean Room ' . $room->room_number,
                        'assigned_to' => $i % 2 === 0 ? 'Sita' : 'Gita',
                        'status' => $i % 3 === 0 ? 'in_progress' : 'queue',
                    ]
                );
            }
        }

        $this->command->info("Hotel Dashboard Seeded for " . $providers->count() . " properties.");
    }
}