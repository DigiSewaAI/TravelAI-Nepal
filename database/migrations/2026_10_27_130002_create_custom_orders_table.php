<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('custom_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('client_name', 100);
            $table->string('client_email', 150)->nullable();
            $table->string('type', 50); // Painting, Sculpture, Handicraft, Mural
            $table->decimal('budget', 12, 2)->default(0);
            $table->date('deadline');
            $table->string('status', 20)->default('pending'); // pending, progress, done
            $table->timestamps();

            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_orders');
    }
};