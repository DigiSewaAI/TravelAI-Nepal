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
                'timeout' => 60,
            ])
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a Nepal trek itinerary planner. You MUST respond with valid JSON only. No explanations, no markdown, no extra text. ONLY JSON.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.3, // ✅ घटाइयो (0.4 → 0.3) – अझ सटीक
                'max_tokens' => 6000,
                'response_format' => ['type' => 'json_object'],
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

            // ✅ JSON Extract (markdown/plain text बाट)
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
     * Extract JSON from a string that may contain markdown or extra text.
     */
    protected function extractJson(string $content): array
    {
        // Try direct JSON decode first
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Try to extract JSON from markdown code blocks
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $jsonString = trim($matches[1]);
            $decoded = json_decode($jsonString, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Try to find anything that looks like a JSON object
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $jsonString = trim($matches[0]);
            $decoded = json_decode($jsonString, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // If everything fails, throw an exception with details
        Log::error('Failed to extract JSON', [
            'content' => $content,
            'json_error' => json_last_error_msg()
        ]);

        throw new \Exception('Failed to validate JSON. Please adjust your prompt. See "failed_generation" for more details.');
    }
}