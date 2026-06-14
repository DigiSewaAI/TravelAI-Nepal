<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('trekker_id')->constrained()->onDelete('cascade');
        $table->foreignId('trek_id')->constrained()->onDelete('cascade');
        $table->date('booking_date');
        $table->date('start_date');
        $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled']);
        $table->string('qr_code')->unique();
        $table->string('invoice_url')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
