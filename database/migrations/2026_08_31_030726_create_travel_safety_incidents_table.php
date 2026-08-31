<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('travel_safety_incidents', function (Blueprint $table) {   // ✅ यहाँ table name सही छ
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('incident_type');
            $table->text('description')->nullable();
            $table->string('severity')->nullable();
            $table->string('status')->default('detected');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            $table->string('district')->nullable();
            $table->string('province')->nullable();
            $table->integer('affected_radius')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->float('confidence_score')->default(0);
            $table->boolean('official_confirmation')->default(false);
            $table->string('travel_impact')->nullable();
            $table->text('recommended_action')->nullable();
            $table->json('raw_source_reference')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'severity']);
            $table->index(['latitude', 'longitude']);
            $table->index('incident_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('travel_safety_incidents');
    }
};