<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained()->onDelete('cascade');
            $table->string('type'); // permit, conservation_fee, local_transport, food_estimate, guide_estimate, accommodation_estimate, porter, etc.
            $table->string('name')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('NPR');
            $table->string('unit')->default('per_person'); // per_person, per_group, per_day, per_km
            $table->boolean('is_mandatory')->default(true);
            $table->json('metadata')->nullable(); // source, source_url, verified_at, notes
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['route_id', 'type', 'effective_from']);
            $table->index(['route_id', 'effective_from', 'effective_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_costs');
    }
};