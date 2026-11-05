<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_claim_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('claimable_type', 100);
            $table->unsignedBigInteger('claimable_id');
            $table->unsignedBigInteger('traveler_id');
            $table->string('contact_method', 20);
            $table->string('contact_value', 190);
            $table->string('otp_hash', 64)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->unsignedTinyInteger('otp_attempts')->default(0);
            $table->timestamp('otp_sent_at')->nullable();
            $table->enum('status', ['pending', 'verified', 'expired', 'locked', 'cancelled'])->default('pending');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index(['claimable_type', 'claimable_id', 'status'], 'idx_active_attempts');
            $table->index(['otp_expires_at', 'status'], 'idx_expiry_cleanup');
            $table->index(['traveler_id', 'created_at'], 'idx_attempts_traveler');
            $table->foreign('traveler_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_claim_attempts');
    }
};