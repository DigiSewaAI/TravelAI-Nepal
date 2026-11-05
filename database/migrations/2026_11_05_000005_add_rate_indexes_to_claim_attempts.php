<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_claim_attempts', function (Blueprint $table) {
            // Rate-limit indexes (no data change)
            $table->index(
                ['claimable_type', 'claimable_id', 'status', 'created_at'],
                'idx_rl_order'
            );
            $table->index(
                ['traveler_id', 'status', 'created_at'],
                'idx_rl_user'
            );
        });
    }

    public function down(): void
    {
        Schema::table('booking_claim_attempts', function (Blueprint $table) {
            $table->dropIndex('idx_rl_order');
            $table->dropIndex('idx_rl_user');
        });
    }
};