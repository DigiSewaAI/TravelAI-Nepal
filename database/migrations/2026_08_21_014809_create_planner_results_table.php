<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planner_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('planner_requests')->onDelete('cascade');
            $table->longText('raw_ai_response')->nullable();
            $table->string('model')->nullable();
            $table->string('model_version')->nullable();
            $table->string('prompt_version')->nullable();
            $table->json('route_snapshot')->nullable();
            $table->enum('validation_status', ['pending', 'valid', 'invalid', 'fallback'])->default('pending');
            $table->boolean('fallback_used')->default(false);
            $table->json('validation_errors')->nullable();
            $table->timestamps();

            $table->index('request_id');
            $table->index('validation_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planner_results');
    }
};