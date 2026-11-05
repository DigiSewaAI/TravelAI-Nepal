<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_claim_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_claim_id')->nullable();
            $table->unsignedBigInteger('booking_claim_attempt_id')->nullable();
            $table->unsignedBigInteger('traveler_id')->nullable();
            $table->string('event_type', 30);
            $table->string('claimable_type', 100)->nullable();
            $table->unsignedBigInteger('claimable_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');

            $table->index(['claimable_type', 'claimable_id'], 'idx_events_booking');
            $table->index(['traveler_id', 'created_at'], 'idx_events_traveler');
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_claim_events');
    }
};