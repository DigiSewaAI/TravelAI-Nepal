<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('booking_usage')) {
            Schema::create('booking_usage', function (Blueprint $table) {
                $table->id();
                $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
                $table->string('month', 7);
                $table->integer('count')->default(0);
                $table->timestamps();

                $table->unique(['provider_id', 'month']);
                $table->index(['provider_id', 'month']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_usage');
    }
};