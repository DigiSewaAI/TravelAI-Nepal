<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('provider_staff', function (Blueprint $t) {
            if (!Schema::hasColumn('provider_staff','skills')) $t->text('skills')->nullable()->after('role');
            if (!Schema::hasColumn('provider_staff','languages')) $t->string('languages', 255)->nullable()->after('skills');
            if (!Schema::hasColumn('provider_staff','experience_years')) $t->unsignedSmallInteger('experience_years')->nullable()->after('languages');
            if (!Schema::hasColumn('provider_staff','availability')) $t->string('availability', 50)->nullable()->after('experience_years');
            if (!Schema::hasColumn('provider_staff','bio')) $t->text('bio')->nullable()->after('availability');
        });
    }
    public function down(): void {
        Schema::table('provider_staff', function (Blueprint $t) {
            foreach (['skills','languages','experience_years','availability','bio'] as $c) {
                if (Schema::hasColumn('provider_staff', $c)) $t->dropColumn($c);
            }
        });
    }
};