<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->integer('count')->default(0);
            $table->string('month'); // '2026-08'
            $table->timestamps();
            
            $table->unique(['provider_id', 'month']);
            $table->index(['provider_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage');
    }
};