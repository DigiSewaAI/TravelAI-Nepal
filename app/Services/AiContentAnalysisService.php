<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Review;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentAnalysisService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
    }

    /**
     * Analyze service description and extract tags/keywords
     */
    public function analyzeDescription(Service $service): array
    {
        try {
            $prompt = "Analyze this tourism service description and extract:
1. Key activities (max 5)
2. Best season (1-2 seasons)
3. Difficulty level (Easy/Moderate/Hard)
4. Recommended group size
5. Key attractions (max 3)

Description: " . $service->description;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/v1/chat/completions', [
                'model' => 'llama3-8b-8192',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a tourism expert. Analyze the description and extract structured information.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'] ?? '';
                return $this->parseAnalysis($content);
            }

            return $this->defaultAnalysis();

        } catch (\Exception $e) {
            Log::error('AI Content Analysis failed: ' . $e->getMessage());
            return $this->defaultAnalysis();
        }
    }

    /**
     * Analyze sentiment of a review
     */
    public function analyzeSentiment(Review $review): array
    {
        try {
            $prompt = "Analyze this review and return sentiment (positive/neutral/negative), confidence score (0-1), and key themes:
Review: " . $review->comment;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/v1/chat/completions', [
                'model' => 'llama3-8b-8192',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a sentiment analysis expert. Analyze the review.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.1,
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'] ?? '';
                return $this->parseSentiment($content);
            }

            return ['sentiment' => 'neutral', 'confidence' => 0.5, 'themes' => []];

        } catch (\Exception $e) {
            Log::error('Sentiment analysis failed: ' . $e->getMessage());
            return ['sentiment' => 'neutral', 'confidence' => 0.5, 'themes' => []];
        }
    }

    protected function parseAnalysis(string $content): array
    {
        return [
            'activities' => $this->extractList($content, 'Key activities'),
            'season' => $this->extractValue($content, 'Best season'),
            'difficulty' => $this->extractValue($content, 'Difficulty level'),
            'group_size' => $this->extractValue($content, 'Recommended group size'),
            'attractions' => $this->extractList($content, 'Key attractions'),
        ];
    }

    protected function parseSentiment(string $content): array
    {
        $sentiment = 'neutral';
        $confidence = 0.5;
        $themes = [];

        if (stripos($content, 'positive') !== false) $sentiment = 'positive';
        elseif (stripos($content, 'negative') !== false) $sentiment = 'negative';

        preg_match('/confidence.*?(\d+\.?\d*)/i', $content, $matches);
        if (isset($matches[1])) $confidence = min((float)$matches[1], 1);

        preg_match('/themes?:?\s*(.*?)$/i', $content, $matches);
        if (isset($matches[1])) {
            $themes = array_map('trim', explode(',', $matches[1]));
        }

        return ['sentiment' => $sentiment, 'confidence' => $confidence, 'themes' => $themes];
    }

    protected function extractValue(string $content, string $key): string
    {
        preg_match('/' . preg_quote($key) . ':\s*(.*?)(?:\n|$)/i', $content, $matches);
        return trim($matches[1] ?? '');
    }

    protected function extractList(string $content, string $key): array
    {
        preg_match('/' . preg_quote($key) . ':\s*(.*?)(?:\n\n|\n\s*\n|$)/is', $content, $matches);
        if (isset($matches[1])) {
            $items = explode('-', $matches[1]);
            return array_map('trim', array_filter($items));
        }
        return [];
    }

    protected function defaultAnalysis(): array
    {
        return [
            'activities' => [],
            'season' => 'Spring/Autumn',
            'difficulty' => 'Moderate',
            'group_size' => '2-6',
            'attractions' => [],
        ];
    }
}