<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('wholesale_rfqs', function (Blueprint $t) {
            if (!Schema::hasColumn('wholesale_rfqs', 'delivery_charge')) {
                $t->decimal('delivery_charge', 10, 2)->nullable()->after('quoted_total');
            }
        });
    }
    public function down(): void {
        Schema::table('wholesale_rfqs', function (Blueprint $t) {
            if (Schema::hasColumn('wholesale_rfqs', 'delivery_charge')) {
                $t->dropColumn('delivery_charge');
            }
        });
    }
};