<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mappings = [
            'trekking-agency'    => ['trek', 'tour', 'activity', 'experience'],
            'tour-agency'        => ['tour', 'trek', 'experience'],
            'hotel'              => ['hotel'],
            'resort'             => ['resort'],
            'lodge'              => ['lodge'],
            'homestay'           => ['homestay', 'experience'],
            'guide'              => ['guide'],
            'porter'             => ['guide'],
            'transport-provider' => ['transport'],
            'activity-provider'  => ['activity', 'experience'],
            'local-experience'   => ['experience'],
            'photographer'       => ['experience'],
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
        DB::table('provider_type_service_category')->truncate();
    }
};