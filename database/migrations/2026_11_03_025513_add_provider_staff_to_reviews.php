<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('reviews', function (Blueprint $t) {
            if (!Schema::hasColumn('reviews', 'provider_id')) {
                $t->foreignId('provider_id')->nullable()->after('service_id')->constrained('providers')->nullOnDelete();
            }
            if (!Schema::hasColumn('reviews', 'staff_id')) {
                $t->foreignId('staff_id')->nullable()->after('provider_id')->constrained('provider_staff')->nullOnDelete();
            }
        });
    }
    public function down(): void {
        Schema::table('reviews', function (Blueprint $t) {
            foreach (['provider_id', 'staff_id'] as $c) {
                if (Schema::hasColumn('reviews', $c)) {
                    $t->dropForeign([$c]);
                    $t->dropColumn($c);
                }
            }
        });
    }
};