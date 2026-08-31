<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('incident_affectables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id');
            $table->morphs('affectable'); // यसले affectable_type, affectable_id र तिनको index बनाउँछ
            $table->float('distance')->nullable();
            $table->string('match_type')->nullable();
            $table->float('confidence')->default(0.5);
            $table->json('metadata')->nullable();
            $table->timestamps();

            // ✅ छोटो unique index name (duplicate हटाइयो)
            $table->unique(['incident_id', 'affectable_type', 'affectable_id'], 'incident_affectables_unique');
            // ✅ morphs() ले पहिले नै affectable_type + affectable_id मा index बनाइसकेको छ, त्यसैले तलको लाइन हटाइयो
        });
    }

    public function down()
    {
        Schema::dropIfExists('incident_affectables');
    }
};