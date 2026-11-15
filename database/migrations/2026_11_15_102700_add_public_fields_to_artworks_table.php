<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artworks', function (Blueprint $table) {
            if (!Schema::hasColumn('artworks', 'image_path')) {
                $table->string('image_path')->nullable()->after('gradient');
            }
            if (!Schema::hasColumn('artworks', 'additional_images')) {
                $table->json('additional_images')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('artworks', 'description')) {
                $table->text('description')->nullable()->after('additional_images');
            }
            if (!Schema::hasColumn('artworks', 'materials')) {
                $table->string('materials')->nullable()->after('description');
            }
            if (!Schema::hasColumn('artworks', 'dimensions')) {
                $table->string('dimensions')->nullable()->after('materials');
            }
            if (!Schema::hasColumn('artworks', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('dimensions');
            }
            if (!Schema::hasColumn('artworks', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('is_published');
            }
        });

        // Backfill existing 6 artworks → published (they were already visible)
        DB::table('artworks')->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('artworks', function (Blueprint $table) {
            foreach (['published_at','is_published','dimensions','materials','description','additional_images','image_path'] as $col) {
                if (Schema::hasColumn('artworks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};