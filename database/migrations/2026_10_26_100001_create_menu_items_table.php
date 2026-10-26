<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('NPR');
            $table->string('category', 40)->default('main'); // starter|main|dessert|drink|other
            $table->string('emoji', 16)->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_available')->default(true);
            $table->unsignedSmallInteger('prep_time_minutes')->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'is_available']);
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};