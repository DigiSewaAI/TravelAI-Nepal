<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rescue_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();

            // Compliance
            $table->string('aviation_license_number', 100)->nullable();
            $table->string('medical_cert_number', 100)->nullable();
            $table->string('insurance_policy_number', 100)->nullable();

            // Coverage
            $table->json('coverage_districts')->nullable();
            $table->integer('coverage_radius_km')->default(50);

            // Pricing
            $table->decimal('base_rate_npr', 10, 2)->default(0);
            $table->decimal('per_km_rate_npr', 8, 2)->default(0);
            $table->decimal('per_hour_rate_npr', 8, 2)->default(0);
            $table->integer('night_surcharge_pct')->default(30);
            $table->integer('high_altitude_surcharge_pct')->default(50);

            // Capability
            $table->integer('max_altitude_ft')->nullable();
            $table->integer('fleet_size')->default(0);
            $table->boolean('is_24_7')->default(false);
            $table->boolean('is_available')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rescue_providers');
    }
};