<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency')->default('NPR');
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['provider_id', 'service_category_id']);
            $table->index('slug');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};