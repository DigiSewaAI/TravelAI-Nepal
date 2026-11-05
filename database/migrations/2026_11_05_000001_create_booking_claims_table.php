<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claimable_type', 100);
            $table->unsignedBigInteger('claimable_id');
            $table->unsignedBigInteger('traveler_id');
            $table->string('claim_email', 190)->nullable();
            $table->string('claim_phone', 30)->nullable();
            $table->timestamp('claimed_at');
            $table->timestamps();

            $table->unique(['claimable_type', 'claimable_id'], 'uq_booking_owner');
            $table->index('traveler_id');
            $table->foreign('traveler_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_claims');
    }
};