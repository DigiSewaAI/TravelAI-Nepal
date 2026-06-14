<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used by the
    | framework. You may change this value at any time to switch between
    | different providers.
    |
    */
    'default' => env('AI_PROVIDER', 'groq'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the AI providers for your application. You may
    | add any number of providers that support the Laravel AI SDK.
    |
    | Supported drivers: "groq", "openai", "google", "anthropic", "fake"
    |
    */
    'providers' => [
        'groq' => [
            'driver' => 'groq',
            'api_key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'),
            'base_uri' => env('GROQ_BASE_URI', 'https://api.groq.com/openai/v1'),
            'options' => [
                'timeout' => 30,
            ],
        ],

        'openai' => [
            'driver' => 'openai',
            'api_key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
            'base_uri' => env('OPENAI_BASE_URI', 'https://api.openai.com/v1'),
        ],

        'google' => [
            'driver' => 'google',
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-2.0-flash-lite'),
            'base_uri' => env('GEMINI_BASE_URI', 'https://generativelanguage.googleapis.com/v1'),
        ],

        'anthropic' => [
            'driver' => 'anthropic',
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-haiku-20240307'),
            'base_uri' => env('ANTHROPIC_BASE_URI', 'https://api.anthropic.com/v1'),
        ],

        'fake' => [
            'driver' => 'fake',
            'model' => 'fake-model',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | This value defines the maximum number of seconds that the AI provider
    | should wait for a response before timing out. Increase this value
    | for longer generation tasks like long itineraries.
    |
    */
    'timeout' => env('AI_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Default Model Parameters
    |--------------------------------------------------------------------------
    |
    | Here you may set default parameters that will be used across all AI
    | calls unless overridden. Temperature (creativity) ranges from 0 to 1.
    |
    */
    'defaults' => [
        'temperature' => env('AI_TEMPERATURE', 0.7),
        'max_tokens' => env('AI_MAX_TOKENS', 2048),
        'top_p' => env('AI_TOP_P', 1.0),
    ],
];