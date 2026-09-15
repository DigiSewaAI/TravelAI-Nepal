<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Canonical feature entitlements (FROZEN by Master).
     * Only these slugs are valid feature entitlements.
     */
    private array $canonical = [
        'free' => [],
        'professional' => [
            'advanced_dashboard',
            'custom_logo',
        ],
        'business' => [
            'advanced_dashboard',
            'full_analytics',
            'white_label',
            'custom_logo',
        ],
        'enterprise' => [
            'advanced_dashboard',
            'full_analytics',
            'white_label',
            'custom_logo',
            'priority_support',
        ],
    ];

    /**
     * Exact previous feature values (captured from runtime inspection).
     * Used to restore DB state on rollback.
     */
    private array $previous = [
        'free' => [
            'Basic Dashboard',
            '3 Listings',
            '5 AI Requests/mo',
            '10 Bookings/mo',
        ],
        'professional' => [
            'Advanced Dashboard',
            '20 Listings',
            '50 AI Requests/mo',
            '100 Bookings/mo',
            'Custom Logo',
        ],
        'business' => [
            'Full Analytics',
            '100 Listings',
            '500 AI Requests/mo',
            '1000 Bookings/mo',
            'White-label',
            'Custom Logo',
        ],
        'enterprise' => [
            'Unlimited Listings',
            'Unlimited Staff',
            'Unlimited AI',
            'Unlimited Bookings',
            'Priority Support',
            'Custom Logo',
        ],
    ];

    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('plans')) {
            throw new \RuntimeException('plans table missing — aborting feature normalization.');
        }

        foreach ($this->canonical as $slug => $features) {
            $before = DB::table('plans')->where('slug', $slug)->value('features');

            DB::table('plans')
                ->where('slug', $slug)
                ->update(['features' => json_encode(array_values($features))]);

            $after = DB::table('plans')->where('slug', $slug)->value('features');

            Log::info('FIX-05 feature normalization', [
                'slug' => $slug,
                'before' => $before,
                'after' => $after,
            ]);
        }
    }

    public function down(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('plans')) {
            return;
        }

        foreach ($this->previous as $slug => $features) {
            DB::table('plans')
                ->where('slug', $slug)
                ->update(['features' => json_encode(array_values($features))]);
        }
    }
};