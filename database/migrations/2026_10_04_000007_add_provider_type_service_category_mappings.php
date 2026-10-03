<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mappings = [
            'shop-owner'         => ['shop'],
            'rental-provider'    => ['rental'],
            'wholesale-provider' => ['wholesale'],
        ];

        foreach ($mappings as $typeSlug => $categorySlugs) {
            $typeId = DB::table('provider_types')->where('slug', $typeSlug)->value('id');
            if (!$typeId) continue;

            foreach ($categorySlugs as $categorySlug) {
                $catId = DB::table('service_categories')->where('slug', $categorySlug)->value('id');
                if (!$catId) continue;

                DB::table('provider_type_service_category')->updateOrInsert(
                    ['provider_type_id' => $typeId, 'service_category_id' => $catId],
                    []
                );
            }
        }
    }

    public function down(): void
    {
        $typeIds = DB::table('provider_types')
            ->whereIn('slug', ['shop-owner', 'rental-provider', 'wholesale-provider'])
            ->pluck('id')
            ->toArray();

        DB::table('provider_type_service_category')
            ->whereIn('provider_type_id', $typeIds)
            ->delete();
    }
};