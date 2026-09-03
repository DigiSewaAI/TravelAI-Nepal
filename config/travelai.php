<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Location Filtering
    |--------------------------------------------------------------------------
    |
    | When enabled, only services that match the route's location will be
    | considered for recommendations. This prevents unrelated services
    | (e.g., Kathmandu hotel in Annapurna) from appearing.
    |
    */
    'location_filtering_enabled' => env('TRAVELAI_LOCATION_FILTERING_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Quality Threshold (0-10)
    |--------------------------------------------------------------------------
    |
    | Minimum quality score required for a provider to be recommended.
    | Currently unused until Phase 2.
    |
    */
    'quality_threshold' => env('TRAVELAI_QUALITY_THRESHOLD', 6.0),

    /*
    |--------------------------------------------------------------------------
    | Max Recommendations per Day
    |--------------------------------------------------------------------------
    |
    | Maximum number of providers to recommend per day (upper limit, not mandatory).
    |
    */
    'max_recommendations' => env('TRAVELAI_MAX_RECOMMENDATIONS', 3),
];