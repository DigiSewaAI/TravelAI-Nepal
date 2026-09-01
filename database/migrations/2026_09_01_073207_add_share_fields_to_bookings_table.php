<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('visibility', ['private', 'link', 'public'])->default('private');
            $table->string('share_token', 64)->nullable()->unique();
            $table->timestamp('share_enabled_at')->nullable();
            $table->timestamp('share_revoked_at')->nullable();

            $table->index(['visibility', 'share_token']);
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['visibility', 'share_token', 'share_enabled_at', 'share_revoked_at']);
        });
    }
};