<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade');
            $table->foreignId('from_location_id')->constrained('locations')->onDelete('restrict');
            $table->foreignId('to_location_id')->constrained('locations')->onDelete('restrict');
            $table->string('name', 200);
            $table->integer('duration_minutes')->default(0);
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['provider_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_routes');
    }
};