<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Add booking_id
            if (!Schema::hasColumn('reviews', 'booking_id')) {
                $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            }
            // Add user_id
            if (!Schema::hasColumn('reviews', 'user_id')) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
            }
            // Add service_id
            if (!Schema::hasColumn('reviews', 'service_id')) {
                $table->foreignId('service_id')->constrained()->onDelete('cascade');
            }
            // Add rating
            if (!Schema::hasColumn('reviews', 'rating')) {
                $table->tinyInteger('rating')->unsigned()->comment('1-5 stars');
            }
            // Add comment
            if (!Schema::hasColumn('reviews', 'comment')) {
                $table->text('comment')->nullable();
            }
            // Add status
            if (!Schema::hasColumn('reviews', 'status')) {
                $table->string('status')->default('pending')->comment('pending, approved, rejected');
            }

            // Add indexes (optional but good for performance)
            $table->index(['service_id', 'status']);
            $table->unique(['booking_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Drop indexes first
            $table->dropUnique(['booking_id', 'user_id']);
            $table->dropIndex(['service_id', 'status']);

            // Drop foreign keys
            $table->dropForeign(['booking_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['service_id']);

            // Drop columns
            $table->dropColumn(['booking_id', 'user_id', 'service_id', 'rating', 'comment', 'status']);
        });
    }
};