<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hotel_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('hotel_rooms')->cascadeOnDelete();
            $table->string('guest_name', 100);
            $table->string('guest_email', 150)->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('nights')->default(1);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_status', 20)->default('pending'); // paid, pending, refunded
            $table->string('status', 20)->default('pending'); // pending, checked_in, checked_out, cancelled
            $table->timestamps();

            $table->index(['provider_id', 'created_at']);
            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_bookings');
    }
};