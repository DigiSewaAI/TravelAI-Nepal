<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('safety_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // rss, json, html
            $table->string('base_url')->nullable();
            $table->string('feed_url')->nullable();
            $table->string('source_category'); // official, institutional, news, other
            $table->float('reliability_score')->default(0.5);
            $table->boolean('enabled')->default(true);
            $table->integer('fetch_interval')->default(60); // minutes
            $table->string('parser_type')->nullable(); // rss, json_api, html_scraper
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->text('last_error')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('safety_sources');
    }
};