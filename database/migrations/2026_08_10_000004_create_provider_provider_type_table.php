<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_provider_type', function (Blueprint $table) {
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_type_id')->constrained()->onDelete('cascade');
            $table->primary(['provider_id', 'provider_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_provider_type');
    }
};