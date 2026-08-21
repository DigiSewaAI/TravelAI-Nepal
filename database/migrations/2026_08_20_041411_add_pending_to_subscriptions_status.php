<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    DB::statement("ALTER TABLE subscriptions MODIFY status ENUM('active', 'inactive', 'cancelled', 'expired', 'pending')");
}

    /**
     * Reverse the migrations.
     */
    public function down()
{
    DB::statement("ALTER TABLE subscriptions MODIFY status ENUM('active', 'inactive', 'cancelled', 'expired')");
}
};
