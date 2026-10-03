<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $types = [
            ['name' => 'Shop Owner',         'slug' => 'shop-owner',         'description' => 'Handicraft and souvenir shop owners'],
            ['name' => 'Rental Provider',    'slug' => 'rental-provider',    'description' => 'Gear and equipment rental providers'],
            ['name' => 'Wholesale Provider', 'slug' => 'wholesale-provider', 'description' => 'Wholesale and bulk suppliers'],
        ];

        foreach ($types as $type) {
            DB::table('provider_types')->updateOrInsert(
                ['slug' => $type['slug']],
                [
                    'name'                => $type['name'],
                    'description'         => $type['description'],
                    'service_category_id' => null,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('provider_types')
            ->whereIn('slug', ['shop-owner', 'rental-provider', 'wholesale-provider'])
            ->delete();
    }
};