<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        // ✅ qwen मोडेल प्रयोग गर (किनभने त्यो काम गर्छ)
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

    public function generateItinerary(string $prompt): array
    {
        Log::info('Groq API call initiated', [
            'model' => $this->model,
            'prompt_length' => strlen($prompt),
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->withOptions([
                'verify' => false,
                'timeout' => 90, // ✅ बढाइयो timeout
            ])
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a JSON generator. Return ONLY valid JSON. No other text, no markdown, no explanations.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
                'max_tokens' => 5000, // ✅ 5000 मा बढाइयो (पहिले 3000 थियो)
                // ✅ `response_format` हटाइयो – qwen लाई यसले समस्या दिन्छ
            ]);

            Log::info('Groq API response received', [
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if (!$response->successful()) {
                $errorBody = $response->body();
                Log::error('Groq API failed', [
                    'status' => $response->status(),
                    'body' => $errorBody,
                ]);

                $errorData = json_decode($errorBody, true);
                $errorMessage = $errorData['error']['message'] ?? $errorBody;

                throw new \Exception("Groq API error: " . $errorMessage);
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';

            Log::info('Groq response content preview', [
                'content_preview' => substr($content, 0, 500)
            ]);

            $parsed = $this->extractJson($content);
            return $parsed;
        } catch (\Exception $e) {
            Log::error('LlmService exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Extract JSON from a string that may contain markdown, <think> tags, or extra text.
     * Improved: removes <think> tags, extracts the first complete JSON object,
     * and attempts to fix incomplete JSON by adding missing braces.
     */
    protected function extractJson(string $content): array
    {
        Log::info('LLM Raw Response', ['content' => $content]);

        // 1. Remove <think> ... </think> (including multiline)
        $cleaned = preg_replace('/<think>.*?<\/think>/s', '', $content);

        // 2. Remove any other HTML-like tags
        $cleaned = preg_replace('/<[^>]+>/', '', $cleaned);

        // 3. Find the first { and the last } – extract JSON candidate
        if (preg_match('/\{[\s\S]*\}/', $cleaned, $matches)) {
            $jsonCandidate = $matches[0];
        } else {
            // If no braces found, treat the whole cleaned string as candidate
            $jsonCandidate = $cleaned;
        }

        // 4. Fix incomplete JSON: add missing closing braces if needed
        $open = substr_count($jsonCandidate, '{');
        $close = substr_count($jsonCandidate, '}');
        if ($open > $close) {
            $jsonCandidate .= str_repeat('}', $open - $close);
            Log::warning('Added missing braces to JSON: added ' . ($open - $close) . ' braces.');
        }

        // 5. Try to decode
        $decoded = json_decode($jsonCandidate, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // 6. If still fails, try to remove markdown code blocks and re-extract
        $cleaned = preg_replace('/```(?:json)?\s*([\s\S]*?)\s*```/', '$1', $content);
        if (preg_match('/\{[\s\S]*\}/', $cleaned, $matches)) {
            $jsonCandidate = $matches[0];
            $open = substr_count($jsonCandidate, '{');
            $close = substr_count($jsonCandidate, '}');
            if ($open > $close) {
                $jsonCandidate .= str_repeat('}', $open - $close);
            }
            $decoded = json_decode($jsonCandidate, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                Log::warning('Fixed JSON after markdown removal and brace adjustment.');
                return $decoded;
            }
        }

        // 7. Log error and throw
        Log::error('Failed to extract JSON', [
            'content_preview' => substr($content, 0, 500),
            'cleaned_preview' => substr($cleaned, 0, 500),
            'json_error' => json_last_error_msg()
        ]);

        throw new \Exception('Failed to validate JSON. Please adjust your prompt.');
    }
}