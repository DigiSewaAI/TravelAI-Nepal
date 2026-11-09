<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RescueProviderTypeMappingSeeder extends Seeder
{
    public function run(): void
    {
        $pt = DB::table('provider_types')->where('slug', 'rescue-service')->first();
        $sc = DB::table('service_categories')->where('slug', 'rescue')->first();

        if (!$pt || !$sc) {
            $this->command->warn('Missing rescue-service provider type or rescue category. Skipping.');
            return;
        }

        DB::table('provider_type_service_category')->updateOrInsert(
            ['provider_type_id' => $pt->id, 'service_category_id' => $sc->id],
            ['provider_type_id' => $pt->id, 'service_category_id' => $sc->id]
        );

        $this->command->info("Mapped provider_type=rescue-service → service_category=rescue");
    }
}