<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('provider_payment_methods', 'is_primary')) {
            Schema::table('provider_payment_methods', function (Blueprint $table) {
                $table->boolean('is_primary')->default(false)->after('is_active');
            });

            $idx = \DB::select("SHOW INDEX FROM provider_payment_methods WHERE Key_name = 'ppm_provider_primary_idx'");
            if (empty($idx)) {
                Schema::table('provider_payment_methods', function (Blueprint $table) {
                    $table->index(['provider_id', 'is_primary'], 'ppm_provider_primary_idx');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('provider_payment_methods', 'is_primary')) {
            $idx = \DB::select("SHOW INDEX FROM provider_payment_methods WHERE Key_name = 'ppm_provider_primary_idx'");
            Schema::table('provider_payment_methods', function (Blueprint $table) use ($idx) {
                if (!empty($idx)) {
                    $table->dropIndex('ppm_provider_primary_idx');
                }
                $table->dropColumn('is_primary');
            });
        }
    }
};