<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Services: 2-layer rating (individual service reputation)
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'average_rating')) {
                $table->decimal('average_rating', 3, 2)->default(0.00);
            }
            if (!Schema::hasColumn('services', 'reviews_count')) {
                $table->unsignedInteger('reviews_count')->default(0);
            }
            if (!Schema::hasColumn('services', 'rating_breakdown')) {
                $table->json('rating_breakdown')->nullable();
            }
        });

        // Providers: aggregate reputation
        Schema::table('providers', function (Blueprint $table) {
            if (!Schema::hasColumn('providers', 'average_rating')) {
                $table->decimal('average_rating', 3, 2)->default(0.00);
            }
            if (!Schema::hasColumn('providers', 'reviews_count')) {
                $table->unsignedInteger('reviews_count')->default(0);
            }
            if (!Schema::hasColumn('providers', 'rating_breakdown')) {
                $table->json('rating_breakdown')->nullable();
            }
        });
    }

    public function down(): void
    {
        // 🛡️ Non-destructive
    }
};