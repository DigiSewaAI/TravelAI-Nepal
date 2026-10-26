<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Add Provider Types
        $providerTypes = [
            ['name' => 'Restaurant', 'slug' => 'restaurant', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Money Changer', 'slug' => 'money-changer', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Art/Craft', 'slug' => 'art-craft', 'created_at' => now(), 'updated_at' => now()],
        ];
        
        foreach ($providerTypes as $type) {
            DB::table('provider_types')->updateOrInsert(['slug' => $type['slug']], $type);
        }

        // 2. Add Service Categories
        $serviceCategories = [
            ['name' => 'Restaurant', 'slug' => 'restaurant', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Money Changer', 'slug' => 'money-changer', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Art/Craft', 'slug' => 'art-craft', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($serviceCategories as $category) {
            DB::table('service_categories')->updateOrInsert(['slug' => $category['slug']], $category);
        }

        // 3. Map Provider Types to Service Categories (Pivot Table)
        // Note: Table name is likely provider_type_service_category based on Laravel conventions
        $mappings = [
            'restaurant' => 'restaurant',
            'money-changer' => 'money-changer',
            'art-craft' => 'art-craft',
            // Also map existing ones if they are missing (optional, but good for consistency)
            'trekking-agency' => 'trek',
            'tour-agency' => 'tour',
            'hotel' => 'hotel',
            'transport-provider' => 'transport',
            'activity-provider' => 'activity',
            'local-experience' => 'experience',
        ];

        foreach ($mappings as $providerSlug => $categorySlug) {
            $providerType = DB::table('provider_types')->where('slug', $providerSlug)->first();
            $category = DB::table('service_categories')->where('slug', $categorySlug)->first();

            if ($providerType && $category) {
                // Check if table name is provider_type_service_category
                DB::table('provider_type_service_category')->updateOrInsert([
                    'provider_type_id' => $providerType->id,
                    'service_category_id' => $category->id,
                ]);
            }
        }
    }
}