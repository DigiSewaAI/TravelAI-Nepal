<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('plans', 'requires_contact')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->boolean('requires_contact')->default(false)->after('features');
            });
        }

        // Mark Enterprise as contact-only
        DB::table('plans')->where('slug', 'enterprise')->update(['requires_contact' => true]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('plans', 'requires_contact')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('requires_contact');
            });
        }
    }
};