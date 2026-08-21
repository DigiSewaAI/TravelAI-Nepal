<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planner_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id')->nullable();
            $table->foreignId('route_id')->nullable()->constrained()->onDelete('set null');
            $table->string('destination')->nullable();
            $table->integer('days');
            $table->decimal('budget', 10, 2);
            $table->enum('travel_style', ['budget', 'mid_range', 'luxury', 'backpacker']);
            $table->json('interests')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('session_id');
            $table->index('route_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planner_requests');
    }
};