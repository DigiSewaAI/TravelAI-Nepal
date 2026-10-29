<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrphanTypesFixSeeder extends Seeder
{
    public function run(): void
    {
        echo "═══ OrphanTypesFixSeeder ═══\n";

        // 1. Porter + Photographer service categories (idempotent)
        $newCats = [
            ['name' => 'Porter',       'slug' => 'porter',       'description' => 'Trekking porters'],
            ['name' => 'Photographer', 'slug' => 'photographer', 'description' => 'Photography services'],
        ];
        foreach ($newCats as $cat) {
            if (DB::table('service_categories')->where('slug', $cat['slug'])->exists()) {
                echo "  SC '{$cat['slug']}' exists - skip\n";
            } else {
                DB::table('service_categories')->insert($cat);
                echo "  SC '{$cat['slug']}' created\n";
            }
        }

        // 2. Six missing pivot mappings (idempotent)
        $mappings = [
            'resort'       => 'resort',
            'lodge'        => 'lodge',
            'homestay'     => 'homestay',
            'guide'        => 'guide',
            'porter'       => 'porter',
            'photographer' => 'photographer',
        ];

        foreach ($mappings as $ptSlug => $scSlug) {
            $pt = DB::table('provider_types')->where('slug', $ptSlug)->first();
            $sc = DB::table('service_categories')->where('slug', $scSlug)->first();

            if (!$pt) { echo "  PT '$ptSlug' MISSING\n"; continue; }
            if (!$sc) { echo "  SC '$scSlug' MISSING\n"; continue; }

            $exists = DB::table('provider_type_service_category')
                ->where('provider_type_id', $pt->id)
                ->where('service_category_id', $sc->id)
                ->exists();

            if ($exists) {
                echo "  Pivot PT#{$pt->id}[$ptSlug] -> SC#{$sc->id}[$scSlug] exists - skip\n";
            } else {
                DB::table('provider_type_service_category')->insert([
                    'provider_type_id'    => $pt->id,
                    'service_category_id' => $sc->id,
                ]);
                echo "  Pivot PT#{$pt->id}[$ptSlug] -> SC#{$sc->id}[$scSlug] created\n";
            }
        }

        echo "\n  Total pivot rows: " . DB::table('provider_type_service_category')->count() . "\n";
        echo "═══ DONE ═══\n";
    }
}