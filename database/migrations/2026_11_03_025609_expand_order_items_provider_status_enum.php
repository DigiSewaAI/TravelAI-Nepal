<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Additive: extend existing enum (additive only, no data loss)
        DB::statement("ALTER TABLE `order_items` MODIFY COLUMN `provider_status`
            ENUM('pending','confirmed','shipped','delivered','cancelled','picked_up','returned','overdue')
            NOT NULL DEFAULT 'pending'");
    }
    public function down(): void {
        // Revert to original (only if no rows use new values)
        $hasNew = DB::table('order_items')
            ->whereIn('provider_status', ['picked_up', 'returned', 'overdue'])
            ->exists();
        if (!$hasNew) {
            DB::statement("ALTER TABLE `order_items` MODIFY COLUMN `provider_status`
                ENUM('pending','confirmed','shipped','delivered','cancelled')
                NOT NULL DEFAULT 'pending'");
        }
    }
};