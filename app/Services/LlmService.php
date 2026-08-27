<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmService
{
    protected string $apiKey;
    protected string $model;
    protected int $maxRetries = 3;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->model = config('services.groq.model', 'qwen/qwen3.6-27b');

        Log::info('LlmService initialized', [
            'model' => $this->model,
            'api_key_prefix' => substr($this->apiKey ?? '', 0, 10) . '...'
        ]);
    }

    public function listModels(): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])
        ->withOptions(['verify' => false])
        ->get('https://api.groq.com/openai/v1/models');

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
    public function generateItinerary(string $prompt, string $locale = 'en', ?string $model = null, bool $extract = true, int $maxTokens = 3000): array
    {
        Log::info('🔍 [LlmService] generateItinerary called', [
            'locale' => $locale,
            'prompt_preview' => substr($prompt, 0, 500),
            'prompt_length' => strlen($prompt),
        ]);

        $attempt = 0;
        $baseDelay = 2;

        while ($attempt < $this->maxRetries) {
            try {
                $modelToUse = $model ?? $this->model;

                Log::info('Groq API call initiated', [
                    'model' => $modelToUse,
                    'attempt' => $attempt + 1,
                    'prompt_length' => strlen($prompt),
                ]);

                Log::info('🔍 [LlmService] Sending to Groq', [
                    'model' => $modelToUse,
                    'system_prompt' => $this->getSystemPrompt($locale),
                    'user_prompt_preview' => substr($prompt, 0, 300),
                    'temperature' => 0.2,
                ]);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->withOptions([
                    'verify' => false,
                    'timeout' => 120,
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $modelToUse,
                    'messages' => [
                        ['role' => 'system', 'content' => $this->getSystemPrompt($locale)],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => $maxTokens,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['choices'][0]['message']['content'] ?? '';

                    Log::info('🔍 [LlmService] Raw Groq Response', [
                        'raw_content' => $content,
                        'content_length' => strlen($content),
                    ]);

                    // ✅ If extraction is disabled, return raw content as array
                    if (!$extract) {
                        return ['content' => $content, 'raw' => true];
                    }

                    // ✅ Default: extract JSON
                    return $this->extractJson($content);
                }

                if ($response->status() === 429) {
                    $retryAfter = $response->header('Retry-After') ?? ($baseDelay * pow(2, $attempt));
                    Log::warning('Groq rate limit hit', [
                        'attempt' => $attempt + 1,
                        'retry_after' => $retryAfter,
                        'body' => $response->body(),
                    ]);
                    if ((int) $retryAfter > 30) {
                        throw new \Exception('Rate limit: Please wait ' . $retryAfter . ' seconds.');
                    }
                    sleep((int) $retryAfter + 1);
                    $attempt++;
                    continue;
                }

                Log::error('Groq API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception("Groq API error: " . $response->body());

            } catch (\Exception $e) {
                Log::error('LlmService exception', [
                    'attempt' => $attempt + 1,
                    'message' => $e->getMessage(),
                ]);

                if (str_contains($e->getMessage(), 'Rate limit')) {
                    throw $e;
                }

                if ($attempt >= $this->maxRetries - 1) {
                    throw $e;
                }

                $delay = $baseDelay * pow(2, $attempt);
                Log::info("Retrying after {$delay} seconds...");
                sleep($delay);
                $attempt++;
            }
        }

        throw new \Exception('Max retries exceeded for Groq API.');
    }

    protected function extractJson(string $content): array
    {
        Log::info('LLM Raw Response', ['content' => substr($content, 0, 500)]);

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
            'content_preview' => substr($content, 0, 300),
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