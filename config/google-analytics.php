<?php

return [
    /*
     * The Google Analytics Measurement ID (usually starts with 'G-')
     */
    'measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID'),

    /*
     * Whether or not to enable Google Analytics.
     */
    'enabled' => env('GOOGLE_ANALYTICS_ENABLED', true),

    /*
     * Additional Tag IDs that should be loaded.
     */
    'tag_ids' => [],
];
