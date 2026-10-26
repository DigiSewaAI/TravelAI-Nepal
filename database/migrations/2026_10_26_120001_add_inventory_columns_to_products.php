<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 64)->nullable()->unique()->after('slug');
            $table->integer('stock_quantity')->default(0)->after('status');
            $table->unsignedInteger('low_stock_threshold')->default(5)->after('stock_quantity');
            $table->decimal('cost_price', 10, 2)->nullable()->after('price');

            $table->index(['provider_id', 'stock_quantity'], 'products_provider_stock_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_provider_stock_idx');
            $table->dropColumn(['sku', 'stock_quantity', 'low_stock_threshold', 'cost_price']);
        });
    }
};