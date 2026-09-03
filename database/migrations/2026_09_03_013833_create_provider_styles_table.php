<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderStylesTable extends Migration
{
    public function up()
    {
        Schema::create('provider_styles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->string('style_slug', 50);
            $table->timestamps();

            $table->unique(['provider_id', 'style_slug']);
            $table->index('style_slug');
        });
    }

    public function down()
    {
        Schema::dropIfExists('provider_styles');
    }
}