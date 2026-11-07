<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */
    'restaurant_enabled' => env('RESTAURANT_CLAIM_ENABLED', false),
    'transport_enabled'  => env('TRANSPORT_CLAIM_ENABLED', false),
    'hotel_enabled'      => env('HOTEL_CLAIM_ENABLED', false),
    'artcraft_enabled'   => env('ARTCRAFT_CLAIM_ENABLED', false),
    'moneychanger_enabled' => env('MONEYCHANGER_CLAIM_ENABLED', true),
    'individual_enabled' => env('INDIVIDUAL_CLAIM_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | HMAC Secrets (Mandatory for receipt codes)
    |--------------------------------------------------------------------------
    | CLAIM_CODE_SECRET must be set to enable receipt code generation.
    | No APP_KEY fallback in production — fail-fast if missing.
    */
    'code_secret'     => env('CLAIM_CODE_SECRET'),
    'code_secret_old' => env('CLAIM_CODE_SECRET_OLD'),

    /*
    |--------------------------------------------------------------------------
    | Receipt Code Configuration
    |--------------------------------------------------------------------------
    */
    'receipt_code_length' => 8,
    'receipt_code_charset' => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
    'receipt_expiry_days' => 90,
    'max_reissues'        => 3,
    'max_reissues_24h'    => 5,

    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    */
    'otp_length'          => 6,
    'otp_expiry_minutes'  => 5,
    'otp_max_attempts'    => 5,
    'resend_cooldown_sec' => 90,

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'ip_per_15min'  => 5,
        'order_per_day' => 3,
        'user_per_day'  => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Claimable Models (Allowlist — prevents arbitrary model input)
    |--------------------------------------------------------------------------
    */
    'claimable_models' => [
        \App\Models\RestaurantOrder::class  => 'restaurant',
        \App\Models\TransportBooking::class => 'transport',
        \App\Models\HotelBooking::class     => 'hotel',
        \App\Models\CustomOrder::class      => 'artcraft',
        \App\Models\ExchangeTransaction::class => 'moneychanger',
        \App\Models\IndividualBooking::class => 'individual',
    ],
];