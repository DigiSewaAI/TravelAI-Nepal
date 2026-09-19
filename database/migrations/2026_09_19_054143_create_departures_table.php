<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_id')
                  ->constrained('services')
                  ->onDelete('cascade');

            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('capacity');

            $table->enum('status', ['scheduled', 'cancelled'])
                  ->default('scheduled');

            $table->timestamps();

            $table->index(['service_id', 'start_date']);
            $table->index(['service_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departures');
    }
};