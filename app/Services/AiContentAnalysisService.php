<?php

namespace App\Services;

use App\Exceptions\AiQuotaExceededException;
use App\Models\Service;
use App\Models\Review;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentAnalysisService
{
    protected $apiKey;

    public function __construct(
        protected AiReservationService $reservations
    ) {
        $this->apiKey = config('services.groq.api_key');
    }

    /**
     * Analyze service description and extract tags/keywords.
     *
     * FIX: Quota-gated via AiReservationService (provider-bound).
     */
    public function analyzeDescription(Service $service): array
    {
        $provider = $service->provider;

        if (!$provider) {
            Log::warning('AiContentAnalysis: service has no provider', [
                'service_id' => $service->id,
            ]);
            return $this->defaultAnalysis();
        }

        $payload = [
            'service_id'       => $service->id,
            'description_hash' => md5((string) $service->description),
        ];

        $idempotencyKey = AiReservationService::generateIdempotencyKey(
            'provider:' . $provider->id,
            'service.ai.analyze_description',
            $payload
        );

        $reservation = null;
        try {
            $reservation = $this->reservations->reserveForProvider(
                $provider,
                'service.ai.analyze_description',
                $idempotencyKey
            );
        } catch (AiQuotaExceededException $e) {
            Log::info('AiContentAnalysis: provider AI quota exceeded', [
                'provider_id' => $provider->id,
            ]);
            return $this->defaultAnalysis();
        } catch (\Throwable $e) {
            Log::error('AiContentAnalysis: reservation failed', [
                'provider_id' => $provider->id,
                'error'       => $e->getMessage(),
            ]);
            return $this->defaultAnalysis();
        }

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
                'Content-Type'  => 'application/json',
            ])
            ->withOptions([
                'verify' => !app()->environment('local', 'testing'),
            ])
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'       => config('services.groq.model') ?? 'openai/gpt-oss-20b',
                'messages'    => [
                    ['role' => 'system', 'content' => 'You are a tourism expert. Analyze the description and extract structured information.'],
                    ['role' => 'user',   'content' => $prompt],
                ],
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'] ?? '';
                $this->reservations->finalize($reservation);
                return $this->parseAnalysis($content);
            }

            $this->reservations->release($reservation);
            Log::warning('AiContentAnalysis: non-2xx Groq response', [
                'status' => $response->status(),
            ]);
            return $this->defaultAnalysis();

        } catch (\Throwable $e) {
            try {
                $this->reservations->release($reservation);
            } catch (\Throwable $releaseError) {
                Log::error('AiContentAnalysis: failed to release reservation', [
                    'reservation_id' => $reservation?->id,
                    'error'          => $releaseError->getMessage(),
                ]);
            }

            Log::error('AI Content Analysis failed: ' . $e->getMessage());
            return $this->defaultAnalysis();
        }
    }

    /**
     * Analyze sentiment of a review.
     *
     * FIX: Quota-gated via AiReservationService (provider-bound).
     */
    public function analyzeSentiment(Review $review): array
    {
        $defaultSentiment = ['sentiment' => 'neutral', 'confidence' => 0.5, 'themes' => []];

        $provider = $review->service?->provider;

        if (!$provider) {
            Log::warning('AiContentAnalysis: review has no provider chain', [
                'review_id' => $review->id,
            ]);
            return $defaultSentiment;
        }

        $payload = [
            'review_id'    => $review->id,
            'comment_hash' => md5((string) $review->comment),
        ];

        $idempotencyKey = AiReservationService::generateIdempotencyKey(
            'provider:' . $provider->id,
            'service.ai.analyze_sentiment',
            $payload
        );

        $reservation = null;
        try {
            $reservation = $this->reservations->reserveForProvider(
                $provider,
                'service.ai.analyze_sentiment',
                $idempotencyKey
            );
        } catch (AiQuotaExceededException $e) {
            Log::info('AiContentAnalysis: provider AI quota exceeded (sentiment)', [
                'provider_id' => $provider->id,
            ]);
            return $defaultSentiment;
        } catch (\Throwable $e) {
            Log::error('AiContentAnalysis: sentiment reservation failed', [
                'provider_id' => $provider->id,
                'error'       => $e->getMessage(),
            ]);
            return $defaultSentiment;
        }

        try {
            $prompt = "Analyze this review and return sentiment (positive/neutral/negative), confidence score (0-1), and key themes:
Review: " . $review->comment;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->withOptions([
                'verify' => !app()->environment('local', 'testing'),
            ])
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'       => config('services.groq.model') ?? 'openai/gpt-oss-20b',
                'messages'    => [
                    ['role' => 'system', 'content' => 'You are a sentiment analysis expert. Analyze the review.'],
                    ['role' => 'user',   'content' => $prompt],
                ],
                'temperature' => 0.1,
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'] ?? '';
                $this->reservations->finalize($reservation);
                return $this->parseSentiment($content);
            }

            $this->reservations->release($reservation);
            Log::warning('AiContentAnalysis: non-2xx Groq response (sentiment)', [
                'status' => $response->status(),
            ]);
            return $defaultSentiment;

        } catch (\Throwable $e) {
            try {
                $this->reservations->release($reservation);
            } catch (\Throwable $releaseError) {
                Log::error('AiContentAnalysis: failed to release sentiment reservation', [
                    'reservation_id' => $reservation?->id,
                    'error'          => $releaseError->getMessage(),
                ]);
            }

            Log::error('Sentiment analysis failed: ' . $e->getMessage());
            return $defaultSentiment;
        }
    }

    protected function parseAnalysis(string $content): array
    {
        return [
            'activities'  => $this->extractList($content, 'Key activities'),
            'season'      => $this->extractValue($content, 'Best season'),
            'difficulty'  => $this->extractValue($content, 'Difficulty level'),
            'group_size'  => $this->extractValue($content, 'Recommended group size'),
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
            'activities'  => [],
            'season'      => 'Spring/Autumn',
            'difficulty'  => 'Moderate',
            'group_size'  => '2-6',
            'attractions' => [],
        ];
    }
}