<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Safety: Skip if columns already exist
        if (Schema::hasColumn('ads', 'type')) {
            return;
        }

        Schema::table('ads', function (Blueprint $table) {
            // 🆕 Type: 'image' (default) or 'video' (rescue only)
            $table->enum('type', ['image', 'video'])
                  ->default('image')
                  ->after('image_path');

            // 🆕 Video Path (nullable — only for video ads)
            $table->string('video_path')
                  ->nullable()
                  ->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            if (Schema::hasColumn('ads', 'video_path')) {
                $table->dropColumn('video_path');
            }
            if (Schema::hasColumn('ads', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};