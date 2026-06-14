<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('treks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('agency_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->integer('duration_days');
        $table->enum('difficulty', ['easy', 'moderate', 'hard']);
        $table->decimal('price', 10, 2);
        $table->json('itinerary')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treks');
    }
};
