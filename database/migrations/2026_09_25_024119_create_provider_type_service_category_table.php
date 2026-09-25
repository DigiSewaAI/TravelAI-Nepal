<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_type_service_category', function (Blueprint $table) {
            $table->foreignId('provider_type_id')
                  ->constrained('provider_types')
                  ->onDelete('cascade');

            $table->foreignId('service_category_id')
                  ->constrained('service_categories')
                  ->onDelete('cascade');

            $table->primary(['provider_type_id', 'service_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_type_service_category');
    }
};