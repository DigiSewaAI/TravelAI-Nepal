<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $categories = [
            ['name' => 'Resort',   'slug' => 'resort',   'description' => 'Resort accommodations'],
            ['name' => 'Lodge',    'slug' => 'lodge',    'description' => 'Trekking lodges and guesthouses'],
            ['name' => 'Homestay', 'slug' => 'homestay', 'description' => 'Family homestay experiences'],
        ];

        foreach ($categories as $cat) {
            DB::table('service_categories')->updateOrInsert(
                ['slug' => $cat['slug']],
                [
                    'name'        => $cat['name'],
                    'description' => $cat['description'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        foreach (['resort', 'lodge', 'homestay'] as $slug) {
            $cat = DB::table('service_categories')->where('slug', $slug)->first();
            if ($cat) {
                $inUse = DB::table('services')->where('service_category_id', $cat->id)->exists();
                if (!$inUse) {
                    DB::table('service_categories')->where('id', $cat->id)->delete();
                }
            }
        }
    }
};