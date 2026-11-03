<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $matrix = [
            'Free' => ['max_treks'=>3,'max_itinerary_days'=>10,'max_hotels'=>1,'max_rooms'=>5,'max_menu_items'=>5,'max_tables'=>3,'max_vehicles'=>5,'max_routes'=>3,'max_guides'=>3,'max_currencies'=>5,'max_artworks'=>5,'max_products'=>5,'max_staff'=>1,'max_bookings'=>10,'max_ai_requests'=>5],
            'Professional' => ['max_treks'=>20,'max_itinerary_days'=>30,'max_hotels'=>5,'max_rooms'=>30,'max_menu_items'=>30,'max_tables'=>10,'max_vehicles'=>30,'max_routes'=>15,'max_guides'=>20,'max_currencies'=>20,'max_artworks'=>30,'max_products'=>30,'max_staff'=>5,'max_bookings'=>100,'max_ai_requests'=>50],
            'Business' => ['max_treks'=>100,'max_itinerary_days'=>100,'max_hotels'=>20,'max_rooms'=>200,'max_menu_items'=>200,'max_tables'=>30,'max_vehicles'=>200,'max_routes'=>50,'max_guides'=>100,'max_currencies'=>50,'max_artworks'=>200,'max_products'=>200,'max_staff'=>20,'max_bookings'=>1000,'max_ai_requests'=>500],
            'Enterprise' => ['max_treks'=>-1,'max_itinerary_days'=>-1,'max_hotels'=>-1,'max_rooms'=>-1,'max_menu_items'=>-1,'max_tables'=>-1,'max_vehicles'=>-1,'max_routes'=>-1,'max_guides'=>-1,'max_currencies'=>-1,'max_artworks'=>-1,'max_products'=>-1,'max_staff'=>-1,'max_bookings'=>-1,'max_ai_requests'=>5000],
        ];

        foreach ($matrix as $name => $limits) {
            $p = DB::table('plans')->where('name', $name)->first();
            if (!$p) continue;
            $existing = json_decode($p->limits, true) ?: [];
            DB::table('plans')->where('id', $p->id)->update([
                'limits' => json_encode(array_merge($existing, $limits)),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // No-op: safe revert नै
    }
};