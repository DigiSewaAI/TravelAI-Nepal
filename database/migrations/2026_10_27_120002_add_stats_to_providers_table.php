<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->integer('profile_views')->default(0)->after('logo_url');
            $table->integer('completed_jobs')->default(0)->after('profile_views');
            $table->integer('repeat_clients_pct')->default(0)->after('completed_jobs');
            $table->integer('experience_years')->default(0)->after('repeat_clients_pct');
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['profile_views', 'completed_jobs', 'repeat_clients_pct', 'experience_years']);
        });
    }
};