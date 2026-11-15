<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_orders', 'request_type')) {
                $table->enum('request_type', ['ready_made', 'custom'])->default('custom')->after('booking_number');
            }
            if (!Schema::hasColumn('custom_orders', 'ready_made_artwork_id')) {
                $table->unsignedBigInteger('ready_made_artwork_id')->nullable()->after('request_type');
                $table->foreign('ready_made_artwork_id')->references('id')->on('artworks')->onDelete('set null');
            }
            if (!Schema::hasColumn('custom_orders', 'description')) {
                $table->text('description')->nullable()->after('type');
            }
            if (!Schema::hasColumn('custom_orders', 'materials')) {
                $table->string('materials')->nullable()->after('description');
            }
            if (!Schema::hasColumn('custom_orders', 'dimensions')) {
                $table->string('dimensions')->nullable()->after('materials');
            }
            if (!Schema::hasColumn('custom_orders', 'quantity')) {
                $table->integer('quantity')->default(1)->after('dimensions');
            }
            if (!Schema::hasColumn('custom_orders', 'reference_image_path')) {
                $table->string('reference_image_path')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('custom_orders', 'pickup_address')) {
                $table->string('pickup_address')->nullable()->after('reference_image_path');
            }
            if (!Schema::hasColumn('custom_orders', 'delivery_address')) {
                $table->string('delivery_address')->nullable()->after('pickup_address');
            }
            if (!Schema::hasColumn('custom_orders', 'quoted_amount')) {
                $table->decimal('quoted_amount', 10, 2)->nullable()->after('delivery_address');
            }
            if (!Schema::hasColumn('custom_orders', 'quoted_at')) {
                $table->timestamp('quoted_at')->nullable()->after('quoted_amount');
            }
            if (!Schema::hasColumn('custom_orders', 'accepted_quote_id')) {
                $table->unsignedBigInteger('accepted_quote_id')->nullable()->after('quoted_at');
            }
            if (!Schema::hasColumn('custom_orders', 'accepted_quote_currency')) {
                $table->string('accepted_quote_currency', 3)->nullable()->after('accepted_quote_id');
            }
            if (!Schema::hasColumn('custom_orders', 'production_started_at')) {
                $table->timestamp('production_started_at')->nullable()->after('accepted_quote_currency');
            }
            if (!Schema::hasColumn('custom_orders', 'ready_at')) {
                $table->timestamp('ready_at')->nullable()->after('production_started_at');
            }
            if (!Schema::hasColumn('custom_orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('ready_at');
            }
            if (!Schema::hasColumn('custom_orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('custom_orders', 'cancellation_requested')) {
                $table->boolean('cancellation_requested')->default(false)->after('cancelled_at');
            }
            if (!Schema::hasColumn('custom_orders', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancellation_requested');
            }
            if (!Schema::hasColumn('custom_orders', 'artwork_name_snapshot')) {
                $table->string('artwork_name_snapshot')->nullable()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('custom_orders', 'artwork_price_snapshot')) {
                $table->decimal('artwork_price_snapshot', 10, 2)->nullable()->after('artwork_name_snapshot');
            }
            if (!Schema::hasColumn('custom_orders', 'artwork_currency_snapshot')) {
                $table->string('artwork_currency_snapshot', 3)->default('NPR')->after('artwork_price_snapshot');
            }
        });

        // Expand status enum — preserve existing values, add new states
        DB::statement("ALTER TABLE custom_orders MODIFY COLUMN status ENUM(
            'pending','under_review','quoted','accepted','revision_requested',
            'rejected','in_progress','ready_for_delivery','completed','cancelled'
        ) NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // ⚠️ Down will FAIL if new statuses in use — must clean up first
        DB::statement("ALTER TABLE custom_orders MODIFY COLUMN status ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending'");

        Schema::table('custom_orders', function (Blueprint $table) {
            if (Schema::hasColumn('custom_orders', 'ready_made_artwork_id')) {
                $table->dropForeign(['ready_made_artwork_id']);
                $table->dropColumn('ready_made_artwork_id');
            }
            $cols = ['request_type','description','materials','dimensions','quantity',
                     'reference_image_path','pickup_address','delivery_address',
                     'quoted_amount','quoted_at','accepted_quote_id','accepted_quote_currency',
                     'production_started_at','ready_at','completed_at','cancelled_at',
                     'cancellation_requested','cancellation_reason',
                     'artwork_name_snapshot','artwork_price_snapshot','artwork_currency_snapshot'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('custom_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};