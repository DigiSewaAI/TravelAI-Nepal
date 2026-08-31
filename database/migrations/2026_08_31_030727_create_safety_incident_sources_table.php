<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('safety_incident_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('travel_safety_incidents')->onDelete('cascade');
            $table->foreignId('source_id')->constrained('safety_sources')->onDelete('cascade');
            $table->string('source_url');
            $table->string('source_title')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('retrieved_at')->nullable();
            $table->string('source_type')->nullable();
            $table->float('source_reliability')->default(0.5);
            $table->text('evidence_text')->nullable();
            $table->string('content_hash')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['incident_id', 'source_id', 'source_url']);
            $table->index('content_hash');
        });
    }

    public function down()
    {
        Schema::dropIfExists('safety_incident_sources');
    }
};