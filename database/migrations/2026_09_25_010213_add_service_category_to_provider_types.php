<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1 — Add service_category_id column (nullable + FK)
        Schema::table('provider_types', function (Blueprint $table) {
            $table->unsignedBigInteger('service_category_id')
                  ->nullable()
                  ->after('description');

            $table->foreign('service_category_id')
                  ->references('id')
                  ->on('service_categories')
                  ->onDelete('set null');
        });

        // Step 2 — Seed 12 provider-type → category mappings (Q1)
        $mapping = [
            'trekking-agency'    => 'trek',
            'tour-agency'        => 'tour',
            'hotel'              => 'hotel',
            'resort'             => 'hotel',
            'lodge'              => 'hotel',
            'homestay'           => 'hotel',
            'guide'              => 'guide',
            'porter'             => 'guide',
            'transport-provider' => 'transport',
            'activity-provider'  => 'activity',
            'local-experience'   => 'experience',
            'photographer'       => 'experience',
        ];

        foreach ($mapping as $typeSlug => $categorySlug) {
            $categoryId = DB::table('service_categories')
                            ->where('slug', $categorySlug)
                            ->value('id');

            if ($categoryId) {
                DB::table('provider_types')
                  ->where('slug', $typeSlug)
                  ->update(['service_category_id' => $categoryId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('provider_types', function (Blueprint $table) {
            $table->dropForeign(['service_category_id']);
            $table->dropColumn('service_category_id');
        });
    }
};