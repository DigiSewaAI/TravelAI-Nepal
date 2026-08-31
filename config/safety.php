<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Risk Scoring Weights (sum to 100)
    |--------------------------------------------------------------------------
    */
    'risk_weights' => [
        'severity' => 30,
        'source_confidence' => 20,
        'distance' => 15,
        'official_confirmation' => 15,
        'travel_impact' => 10,
        'recency' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Risk Thresholds (score -> status)
    |--------------------------------------------------------------------------
    */
    'risk_thresholds' => [
        'normal' => 20,
        'caution' => 40,
        'high_risk' => 65,
        'avoid' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Location Matching
    |--------------------------------------------------------------------------
    */
    'matching' => [
        'default_radius' => 5000, // meters
        'max_radius' => 50000,    // 50 km
        'admin_match_fallback' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Stale Data Protection
    |--------------------------------------------------------------------------
    */
    'stale' => [
        'verification_hours' => 24,
        'expiry_days' => 7,
        'confidence_decay_per_day' => 0.1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Deduplication
    |--------------------------------------------------------------------------
    */
    'alert' => [
        'dedup_window_minutes' => 60,
        'resend_on_severity_change' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'geocode_ttl' => 604800, // 7 days
        'status_ttl' => 900,    // 15 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Incident Types (extensible)
    |--------------------------------------------------------------------------
    */
    'incident_types' => [
        'earthquake',
        'flood',
        'flash_flood',
        'glacial_flood',
        'landslide',
        'avalanche',
        'wildfire',
        'storm',
        'heavy_rain',
        'snowstorm',
        'road_closure',
        'bridge_damage',
        'trail_closure',
        'air_traffic_disruption',
        'transport_disruption',
        'health_emergency',
        'security_incident',
        'protest',
        'strike',
        'other',
    ],

    /*
    |--------------------------------------------------------------------------
    | Severity Mapping (from incident severity field to numeric)
    |--------------------------------------------------------------------------
    */
    'severity_map' => [
        'low' => 1,
        'moderate' => 2,
        'high' => 3,
        'critical' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Travel Impact Mapping (to numeric)
    |--------------------------------------------------------------------------
    */
    'travel_impact_map' => [
        'none' => 0,
        'minor' => 1,
        'moderate' => 2,
        'severe' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Source Categories Priority
    |--------------------------------------------------------------------------
    */
    'source_priority' => [
        'official' => 1,
        'institutional' => 2,
        'news' => 3,
        'other' => 4,
    ],
];