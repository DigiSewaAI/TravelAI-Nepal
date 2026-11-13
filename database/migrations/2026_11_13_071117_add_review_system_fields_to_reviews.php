<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Polymorphic — for Order reviews + future extensions
            if (!Schema::hasColumn('reviews', 'reviewable_type')) {
                $table->string('reviewable_type')->nullable()->after('status');
            }
            if (!Schema::hasColumn('reviews', 'reviewable_id')) {
                $table->unsignedBigInteger('reviewable_id')->nullable()->after('reviewable_type');
            }
            if (!Schema::hasColumn('reviews', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->after('booking_id');
            }
            if (!Schema::hasColumn('reviews', 'title')) {
                $table->string('title')->nullable()->after('comment');
            }
            if (!Schema::hasColumn('reviews', 'sub_ratings')) {
                $table->json('sub_ratings')->nullable();
            }
            if (!Schema::hasColumn('reviews', 'photos')) {
                $table->json('photos')->nullable();
            }
            if (!Schema::hasColumn('reviews', 'verified_purchase')) {
                $table->boolean('verified_purchase')->default(false);
            }
            if (!Schema::hasColumn('reviews', 'provider_response')) {
                $table->text('provider_response')->nullable();
            }
            if (!Schema::hasColumn('reviews', 'provider_responded_at')) {
                $table->timestamp('provider_responded_at')->nullable();
            }
            if (!Schema::hasColumn('reviews', 'helpful_count')) {
                $table->unsignedInteger('helpful_count')->default(0);
            }
            if (!Schema::hasColumn('reviews', 'flagged_count')) {
                $table->unsignedInteger('flagged_count')->default(0);
            }
            if (!Schema::hasColumn('reviews', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            if (!Schema::hasColumn('reviews', 'edited_at')) {
                $table->timestamp('edited_at')->nullable();
            }
            if (!Schema::hasColumn('reviews', 'original_rating')) {
                $table->unsignedTinyInteger('original_rating')->nullable();
            }
        });

        // 🛡️ Safeguard: only make nullable if currently NOT NULL and no existing reviews
        if (Schema::hasColumn('reviews', 'booking_id')) {
            try {
                $bookingNotNull = collect(DB::select('SHOW COLUMNS FROM reviews LIKE "booking_id"'))
                    ->first()->Null ?? 'YES';
                if ($bookingNotNull === 'NO' && DB::table('reviews')->count() === 0) {
                    DB::statement('ALTER TABLE reviews MODIFY booking_id BIGINT UNSIGNED NULL');
                }
            } catch (\Throwable $e) {
                \Log::warning('Could not alter booking_id nullability: ' . $e->getMessage());
            }
        }

        // 🛡️ Indexes for polymorphic + order lookups (idempotent)
        Schema::table('reviews', function (Blueprint $table) {
            try { $table->index(['reviewable_type', 'reviewable_id'], 'reviews_reviewable_index'); } catch (\Throwable $e) {}
            try { $table->index(['order_id', 'user_id'], 'reviews_order_user_index'); } catch (\Throwable $e) {}
            try { $table->index(['provider_id', 'status'], 'reviews_provider_status_index'); } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        // 🛡️ Non-destructive rollback (only indexes)
        Schema::table('reviews', function (Blueprint $table) {
            try { $table->dropIndex('reviews_reviewable_index'); } catch (\Throwable $e) {}
            try { $table->dropIndex('reviews_order_user_index'); } catch (\Throwable $e) {}
            try { $table->dropIndex('reviews_provider_status_index'); } catch (\Throwable $e) {}
        });
    }
};