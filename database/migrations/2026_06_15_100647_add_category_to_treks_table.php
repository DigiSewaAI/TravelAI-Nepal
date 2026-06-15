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
    Schema::table('treks', function (Blueprint $table) {
        $table->enum('category', ['trek', 'tour', 'hotel'])->default('trek')->after('difficulty');
    });
}

public function down()
{
    Schema::table('treks', function (Blueprint $table) {
        $table->dropColumn('category');
    });
}
};
