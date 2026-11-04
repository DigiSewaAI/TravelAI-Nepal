<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Environment-based feature flags. Toggle via .env.
    | After changing .env, run: php artisan config:clear (local) OR
    | php artisan config:cache (production).
    |
    */

    'traveler_dashboard_v2' => env('TRAVELER_DASHBOARD_V2', false),
];