<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('artworks', function (Blueprint $t) {
            if (!Schema::hasColumn('artworks','material_cost')) $t->decimal('material_cost',10,2)->nullable()->after('price');
            if (!Schema::hasColumn('artworks','labor_hours')) $t->decimal('labor_hours',5,1)->nullable()->after('material_cost');
            if (!Schema::hasColumn('artworks','labor_cost')) $t->decimal('labor_cost',10,2)->nullable()->after('labor_hours');
            if (!Schema::hasColumn('artworks','craftsmanship_level')) $t->string('craftsmanship_level', 20)->nullable()->after('labor_cost');
        });
    }
    public function down(): void {
        Schema::table('artworks', function (Blueprint $t) {
            foreach (['material_cost','labor_hours','labor_cost','craftsmanship_level'] as $c) {
                if (Schema::hasColumn('artworks', $c)) $t->dropColumn($c);
            }
        });
    }
};