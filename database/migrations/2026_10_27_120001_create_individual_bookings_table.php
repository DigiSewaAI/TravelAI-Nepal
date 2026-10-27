<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('individual_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('service_type', 20); // guide, porter, photographer
            $table->string('client_name', 100);
            $table->string('title', 150);
            $table->integer('pax')->default(1);
            $table->date('start_date');
            $table->string('duration', 50); // e.g., 12 days
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status', 20)->default('pending'); // pending, confirmed, completed
            $table->timestamps();

            $table->index(['provider_id', 'start_date']);
            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('individual_bookings');
    }
};