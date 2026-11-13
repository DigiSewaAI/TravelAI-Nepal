<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // STEP 1: Create missing service_categories
        $categories = [
            ['name' => 'Restaurant',    'slug' => 'restaurant'],
            ['name' => 'Money Changer', 'slug' => 'money-changer'],
            ['name' => 'Art Craft',     'slug' => 'art-craft'],
        ];

        foreach ($categories as $cat) {
            DB::table('service_categories')->updateOrInsert(
                ['slug' => $cat['slug']],
                array_merge($cat, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // STEP 2: Create missing provider_types
        // Note: Rescue Service has NULL category (SOS-based flow)
        $types = [
            ['name' => 'Restaurant',     'slug' => 'restaurant',     'cat_slug' => 'restaurant'],
            ['name' => 'Money Changer',  'slug' => 'money-changer',  'cat_slug' => 'money-changer'],
            ['name' => 'Art Craft',      'slug' => 'art-craft',      'cat_slug' => 'art-craft'],
            ['name' => 'Rescue Service', 'slug' => 'rescue-service', 'cat_slug' => null],
        ];

        foreach ($types as $type) {
            $catId = $type['cat_slug']
                ? DB::table('service_categories')->where('slug', $type['cat_slug'])->value('id')
                : null;

            DB::table('provider_types')->updateOrInsert(
                ['slug' => $type['slug']],
                [
                    'name'                => $type['name'],
                    'service_category_id' => $catId,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        // Non-destructive
    }
};