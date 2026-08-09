<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['manager', 'staff'])->default('staff');
            $table->json('permissions')->nullable(); // custom permissions
            $table->timestamps();

            $table->unique(['user_id', 'provider_id']);
            $table->index('provider_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_staff');
    }
};