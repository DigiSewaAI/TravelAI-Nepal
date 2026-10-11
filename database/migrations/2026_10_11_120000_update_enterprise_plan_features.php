<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $enterprise = DB::table('plans')->where('name', 'Enterprise')->first();
        if (!$enterprise) {
            return;
        }

        $features = json_decode($enterprise->features, true) ?? [];

        $features = array_map(function ($f) {
            return $f === 'Custom Logo' ? 'White-label' : $f;
        }, $features);

        if (!in_array('White-label', $features, true)) {
            $features[] = 'White-label';
        }

        if (!in_array('Priority Support', $features, true)) {
            $features[] = 'Priority Support';
        }

        DB::table('plans')
            ->where('name', 'Enterprise')
            ->update(['features' => json_encode(array_values(array_unique($features)))]);
    }

    public function down(): void
    {
        $enterprise = DB::table('plans')->where('name', 'Enterprise')->first();
        if (!$enterprise) {
            return;
        }

        $features = json_decode($enterprise->features, true) ?? [];

        $features = array_map(function ($f) {
            return $f === 'White-label' ? 'Custom Logo' : $f;
        }, $features);

        DB::table('plans')
            ->where('name', 'Enterprise')
            ->update(['features' => json_encode(array_values(array_unique($features)))]);
    }
};