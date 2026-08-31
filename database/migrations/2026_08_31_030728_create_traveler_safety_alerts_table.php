<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('traveler_safety_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('incident_id')->constrained('travel_safety_incidents')->onDelete('cascade');
            $table->morphs('affectable');
            $table->string('alert_type')->default('incident');
            $table->string('severity')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->string('delivery_channel')->nullable();
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'incident_id']);
            $table->index('sent_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('traveler_safety_alerts');
    }
};