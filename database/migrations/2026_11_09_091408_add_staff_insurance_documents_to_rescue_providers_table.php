<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rescue_providers', function (Blueprint $table) {
            $table->json('staff_members')->nullable();
            $table->json('insurance_partners')->nullable();
            $table->json('documents')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rescue_providers', function (Blueprint $table) {
            $table->dropColumn(['staff_members', 'insurance_partners', 'documents']);
        });
    }
};