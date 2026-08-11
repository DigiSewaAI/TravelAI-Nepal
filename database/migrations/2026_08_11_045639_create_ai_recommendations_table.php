<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->string('type')->default('service')->comment('service, itinerary, package');
            $table->json('recommendations');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'type']);
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};