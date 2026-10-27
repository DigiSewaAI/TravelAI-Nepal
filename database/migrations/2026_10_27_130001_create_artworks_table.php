<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('artworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('artist_name', 100)->nullable();
            $table->string('category', 50)->default('painting'); // painting, sculpture, handicraft
            $table->decimal('price', 12, 2)->default(0);
            $table->string('status', 20)->default('available'); // available, sold, custom
            $table->string('emoji', 10)->nullable();
            $table->string('gradient', 150)->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artworks');
    }
};