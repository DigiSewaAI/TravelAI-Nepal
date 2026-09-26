<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmService
{
        protected string $apiKey;
    protected string $model;
    protected int $maxRetries = 3;

    /** @var array<int, array{name:string,api_keys:array,model:string,base_url:string}> */
    protected array $providers = [];

        public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->model  = config('services.groq.model', 'qwen/qwen3.8-27b');

        $this->providers = $this->buildProviderPool();

        $preferred = config('services.ai.preferred');
        if ($preferred) {
            $this->providers = array_values(array_filter(
                $this->providers,
                fn($p) => $p['name'] === $preferred
            ));
        }

        Log::info('LlmService initialized', [
            'model' => $this->model,
            'api_key_configured' => !empty($this->apiKey),
            'providers_available' => array_values(array_filter(
                array_map(fn($p) => empty($p['api_keys']) ? null : $p['name'], $this->providers)
            )),
        ]);
    }

    protected function buildProviderPool(): array
    {
                return [
            [
                'name'     => 'groq',
                'api_keys' => $this->parseKeys(config('services.groq.api_keys')),
                'model'    => config('services.groq.model', 'qwen/qwen3.8-27b'),
                'models'   => config('services.groq.models', []),
                'base_url' => config('services.groq.base_url', 'https://api.groq.com/openai/v1'),
            ],
            [
                'name'     => 'openrouter',
                'api_keys' => $this->parseKeys(config('services.openrouter.api_keys')),
                'model'    => config('services.openrouter.model', 'meta-llama/llama-3.1-8b-instruct:free'),
                'models'   => config('services.openrouter.models', []),
                'base_url' => config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'),
            ],
            [
                'name'     => 'cerebras',
                'api_keys' => $this->parseKeys(config('services.cerebras.api_keys')),
                'model'    => config('services.cerebras.model', 'llama3.1-8b'),
                'models'   => config('services.cerebras.models', []),
                'base_url' => config('services.cerebras.base_url', 'https://api.cerebras.ai/v1'),
            ],
                        [
                'name'     => 'gemini',
                'api_keys' => $this->parseKeys(config('services.gemini.api_keys')),
                'model'    => config('services.gemini.model', 'gemini-3.5-flash-lite'),
                'models'   => config('services.gemini.models', []),
                'base_url' => config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta/openai'),
            ],
        ];
    }

    protected function parseKeys(?string $raw): array
    {
        if (empty($raw)) return [];
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    protected function callProvider(string $baseUrl, string $apiKey, array $payload, int $timeout = 120): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])
        ->withOptions([
            'verify'  => !app()->environment('local', 'testing'),
            'timeout' => $timeout,
        ])
        ->post(rtrim($baseUrl, '/') . '/chat/completions', $payload);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'status'      => 200,
                'content'     => (string) ($data['choices'][0]['message']['content'] ?? ''),
                'retry_after' => 0,
                'body'        => '',
            ];
        }

        return [
            'status'      => $response->status(),
            'content'     => null,
            'retry_after' => (int) ($response->header('Retry-After') ?: 0),
            'body'        => (string) $response->body(),
        ];
    }

    public function listModels(): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])
                ->withOptions([
            'verify' => !app()->environment('local', 'testing'),
        ])
        ->get(rtrim(config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/') . '/models');

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch models: ' . $response->body());
        }

        $data = $response->json();
        return array_map(function ($model) {
            return $model['id'];
        }, $data['data'] ?? []);
    }

    /**
     * Generate itinerary or any AI response.
     *
     * @param string $prompt
     * @param string $locale
     * @param string|null $model
     * @param bool $extract  If true, extract JSON automatically; if false, return raw content array.
     * @param int $maxTokens Max tokens for the response.
     * @return array
     */
            public function generateItinerary(
        string $prompt,
        string $locale = 'en',
        ?string $model = null,
        bool $extract = true,
        int $maxTokens = 3000,
        float $temperature = 0.2
    ): array {
        Log::info('LlmService generateItinerary called', [
            'locale' => $locale,
            'prompt_length' => strlen($prompt),
        ]);

        $errors = [];
        $sawRateLimit = false;
        $fallbackEnabled = (bool) config('services.ai.fallback_enabled', true);

                foreach ($this->providers as $provider) {
            if (empty($provider['api_keys'])) {
                continue;
            }

            // 4K-F4d: Build models to try (explicit $model > provider models array > single model)
            if ($model !== null) {
                $modelsToTry = [$model];
            } elseif (!empty($provider['models'])) {
                $modelsToTry = $provider['models'];
            } else {
                $modelsToTry = [$provider['model']];
            }

            foreach ($provider['api_keys'] as $keyIdx => $apiKey) {
                foreach ($modelsToTry as $modelIdx => $modelToUse) {
                    $keyLabel = $provider['name'] . '#' . ($keyIdx + 1)
                              . (count($modelsToTry) > 1 ? '/m' . ($modelIdx + 1) : '');

                    $payload = [
                        'model' => $modelToUse,
                        'messages' => [
                            ['role' => 'system', 'content' => $this->getSystemPrompt($locale)],
                            ['role' => 'user',   'content' => $prompt],
                        ],
                        'temperature' => $temperature,
                        'max_tokens'  => $maxTokens,
                    ];
                    if ($extract) {
                        $payload['response_format'] = ['type' => 'json_object'];
                    }

                    Log::info('4J: LLM provider call', [
                        'provider' => $provider['name'],
                        'key'      => $keyLabel,
                        'model'    => $modelToUse,
                    ]);

                                        try {
                        // 4K-F4e: Retry short 429s (burst-limit recovery)
                        $callAttempts = 0;
                        $maxCallAttempts = 2;
                        do {
                            $callAttempts++;
                            $result = $this->callProvider($provider['base_url'], $apiKey, $payload);

                            if ($result['status'] === 429) {
                                $shortRetry = $result['retry_after'] ?: 5;
                                if ($shortRetry <= 10 && $callAttempts < $maxCallAttempts) {
                                    Log::info('4J: Short 429 — retrying same model', [
                                        'provider'    => $provider['name'],
                                        'key'         => $keyLabel,
                                        'model'       => $modelToUse,
                                        'retry_after' => $shortRetry,
                                    ]);
                                    sleep($shortRetry + 1);
                                    continue;
                                }
                            }
                            break;
                        } while (true);

                        if ($result['status'] === 200) {
                            $content = $result['content'];
                            Log::info('4J: LLM response received', [
                                'provider'       => $provider['name'],
                                'key'            => $keyLabel,
                                'content_length' => strlen($content),
                            ]);

                            if (!$extract) {
                                return ['content' => $content, 'raw' => true];
                            }

                            // 4K-F4d: Empty response → try next model (same key)
                            if (strlen(trim((string) $content)) === 0) {
                                Log::warning('4J: Empty response — trying next model', [
                                    'provider' => $provider['name'],
                                    'key'      => $keyLabel,
                                    'model'    => $modelToUse,
                                ]);
                                $errors[] = "{$keyLabel}: empty response";
                                continue;
                            }

                            return $this->extractJson($content);
                        }

                        if ($result['status'] === 429) {
                            $sawRateLimit = true;
                            $retryAfter   = $result['retry_after'] ?: 5;
                            Log::warning('4J: Rate limit hit', [
                                'provider'    => $provider['name'],
                                'key'         => $keyLabel,
                                'retry_after' => $retryAfter,
                            ]);
                            $errors[] = "{$keyLabel}: 429 (retry_after={$retryAfter})";
                            continue;
                        }

                        Log::error('4J: Provider HTTP error', [
                            'provider' => $provider['name'],
                            'key'      => $keyLabel,
                            'status'   => $result['status'],
                        ]);
                        $errors[] = "{$keyLabel}: HTTP {$result['status']}";
                    } catch (\Throwable $e) {
                        Log::error('4J: Provider exception', [
                            'provider' => $provider['name'],
                            'key'      => $keyLabel,
                            'message'  => $e->getMessage(),
                        ]);
                        $errors[] = "{$keyLabel}: " . $e->getMessage();
                    }

                    if (!$fallbackEnabled) {
                        break 3;   // 4K-F4d: 3-level loop
                    }
                }
            }
        }

        $joined = implode(' | ', $errors);
        if ($sawRateLimit) {
            // 4J: lowercase 'rate_limit' for downstream str_contains match
            throw new \Exception('rate_limit: All providers exhausted. ' . $joined);
        }
        throw new \Exception('All providers failed: ' . $joined);
    }

        /**
     * FIX-12: Generate raw text response (no JSON extraction).
     * Used by ItineraryGenerator for free-form itinerary output.
     * Preserves the existing raw-string contract.
     */
    public function generateRawText(
        string $prompt,
        string $systemPrompt = 'You are a helpful assistant.',
        ?string $model = null,
        int $maxTokens = 1500,
        int $timeout = 60
    ): string {
        $modelToUse = $model ?? $this->model;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
                                ->withOptions([
            'verify' => !app()->environment('local', 'testing'),
            'timeout' => $timeout,
        ])
        ->post(
            rtrim(config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/')
                . '/chat/completions',
            [
                'model' => $modelToUse,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => $maxTokens,
            ]
        );

        if (!$response->successful()) {
            Log::error('LlmService::generateRawText failed', [
                'status' => $response->status(),
                'model' => $modelToUse,
            ]);
            throw new \Exception('Groq raw text error: HTTP ' . $response->status());
        }

        $data = $response->json();
        return (string) ($data['choices'][0]['message']['content'] ?? '');
    }

    protected function extractJson(string $content): array
    {
                Log::info('LlmService: LLM response received', [
            'content_length' => strlen($content),
        ]);

        // Remove <think> tags
        $cleaned = preg_replace('/<think>.*?<\/think>/s', '', $content);
        $cleaned = preg_replace('/<[^>]+>/', '', $cleaned);

        // Direct decode
        $decoded = json_decode($cleaned, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Extract from markdown code blocks
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $cleaned, $matches)) {
            $jsonString = trim($matches[1]);
            $decoded = json_decode($jsonString, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Extract the first JSON object
        if (preg_match('/\{[\s\S]*\}/', $cleaned, $matches)) {
            $jsonCandidate = $matches[0];
            $open = substr_count($jsonCandidate, '{');
            $close = substr_count($jsonCandidate, '}');
            if ($open > $close) {
                $jsonCandidate .= str_repeat('}', $open - $close);
            }
            $decoded = json_decode($jsonCandidate, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                Log::info('JSON extracted successfully after fixing braces.');
                return $decoded;
            }
        }

                Log::error('Failed to extract JSON', [
            'content_length' => strlen($content),
            'json_error' => json_last_error_msg()
        ]);

        throw new \Exception('Failed to validate JSON. Please adjust your prompt.');
    }

    protected function getSystemPrompt(string $locale): string
    {
        $basePrompt = 'You are a JSON generator. Respond with a valid JSON object only. No other text. Do NOT include any thinking process, explanations, or markdown. Your entire response must be a single valid JSON object.';

        $languageInstruction = match($locale) {
            'hi' => ' Generate ALL day titles, descriptions, item names, and any text content EXCLUSIVELY in Hindi language (Devanagari script). ONLY waypoint names like "Nayapul" can remain in English. All other text MUST be in Hindi. Do NOT use English for descriptions or item names.',
            'zh' => ' Generate ALL day titles, descriptions, item names, and any text content EXCLUSIVELY in Chinese language (Simplified Chinese characters). ONLY waypoint names like "Nayapul" can remain in English. All other text MUST be in Chinese. Do NOT use English for descriptions or item names.',
            default => ' Generate all content in English.',
        };

        return $basePrompt . $languageInstruction;
    }
}