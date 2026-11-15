<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('providers', 'transport_payment_model')) {
            Schema::table('providers', function (Blueprint $table) {
                $table->enum('transport_payment_model', ['advance', 'pay_on_arrival', 'both'])
                    ->default('both')->after('verification_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('providers', 'transport_payment_model')) {
            Schema::table('providers', function (Blueprint $table) {
                $table->dropColumn('transport_payment_model');
            });
        }
    }
};