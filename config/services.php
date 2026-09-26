<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ✅ Stripe Configuration for Payments (Phase 9)
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret_key' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

        // 🔥 Groq AI Configuration (Phase 4J: multi-key support)
    'groq' => [
        'api_key'  => env('GROQ_API_KEY'),
        'api_keys' => env('GROQ_API_KEYS', env('GROQ_API_KEY')),
        'model'    => env('GROQ_MODEL', 'qwen/qwen3.8-27b'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
    ],

    // 🆕 4J: OpenRouter (free models)
        'openrouter' => [
        'api_key'  => env('OPENROUTER_API_KEY'),
        'api_keys' => env('OPENROUTER_API_KEYS', env('OPENROUTER_API_KEY')),
        'model'    => env('OPENROUTER_MODEL', 'meta-llama/llama-3.1-8b-instruct:free'),
        // 4K-F4d: Multi-model fallback (empty response → next model)
                'models'   => array_filter(array_map('trim', explode(',', env(
            'OPENROUTER_MODELS',
            'google/gemma-4-31b-it:free,google/gemma-4-26b-a4b-it:free'
        )))),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
    ],

    // 🆕 4J: Cerebras (free tier)
    'cerebras' => [
        'api_key'  => env('CEREBRAS_API_KEY'),
        'api_keys' => env('CEREBRAS_API_KEYS', env('CEREBRAS_API_KEY')),
        'model'    => env('CEREBRAS_MODEL', 'llama3.1-8b'),
        'base_url' => env('CEREBRAS_BASE_URL', 'https://api.cerebras.ai/v1'),
    ],

    // 🆕 4J: Provider fallback control
        'ai' => [
        'fallback_enabled' => env('AI_FALLBACK_ENABLED', true),
        'preferred'        => env('AI_PROVIDER'),
    ],

    // Phase CACHE-01: AI draft response caching
    'ai_draft_cache' => [
        'enabled'   => env('AI_DRAFT_CACHE_ENABLED', true),
        'ttl_hours' => env('AI_DRAFT_CACHE_TTL_HOURS', 24),
    ],

    // 🔥 Google Places API (for geocoding & location search)
    'google_places' => [
        'api_key' => env('GOOGLE_PLACES_API_KEY'),
    ],

    // ========== 🔥 PHASE 11: SMS Configuration ==========
    'sms' => [
        'gateway' => env('SMS_GATEWAY', 'twilio'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'nepal_sms' => [
        'api_key' => env('NEPAL_SMS_API_KEY'),
        'sender_id' => env('NEPAL_SMS_SENDER_ID'),
    ],

];