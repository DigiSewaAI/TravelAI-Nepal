<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * FIX-12: Now routes through LlmService (no direct Groq).
 *
 * Preserves the existing string-return contract using ERROR_PREFIX
 * to signal failure without throwing. This lets the controller
 * distinguish success from failure for quota finalize/release.
 */
class ItineraryGenerator
{
    /**
     * Error marker. If the returned string starts with this prefix,
     * the caller must treat the result as a failure.
     */
    public const ERROR_PREFIX = '❌';

    public function __construct(
        protected LlmService $llm
    ) {}

    public function generate(array $data): string
    {
        if (empty(config('services.groq.api_key'))) {
            Log::error('GROQ_API_KEY is missing in config');
            return self::ERROR_PREFIX . ' API key not configured. Please add GROQ_API_KEY to your .env file.';
        }

        $prompt = $this->buildPrompt($data);

        try {
            $content = $this->llm->generateRawText(
                prompt: $prompt,
                systemPrompt: 'You are a professional travel planner for any destination worldwide. Provide detailed, practical itineraries with daily activities, accommodation suggestions, transport options, and local tips. Use local currency where appropriate. Always respond in English.',
                model: 'openai/gpt-oss-20b',
                maxTokens: 1500,
                timeout: 60
            );

            if (trim($content) === '') {
                Log::error('ItineraryGenerator: empty LLM response');
                return self::ERROR_PREFIX . ' Unable to generate itinerary at this moment. Please try again later.';
            }

            return $content;

        } catch (\Exception $e) {
            Log::error('Itinerary generation failed: ' . $e->getMessage());
            return self::ERROR_PREFIX . ' Unable to generate itinerary at this moment. Please try again later.';
        }
    }

    private function buildPrompt(array $data): string
    {
        return "Destination: {$data['destination']}\n"
             . "Number of days: {$data['days']}\n"
             . "Budget: {$data['budget']} (NPR/USD)\n"
             . "Travel style: {$data['travel_style']}\n"
             . "Interests: " . ($data['interests'] ?? 'Not specified') . "\n\n"
             . "Create a detailed day-by-day itinerary for this trip. Include:\n"
             . "- Daily activities and sightseeing\n"
             . "- Recommended accommodation (budget-friendly or luxury as per style)\n"
             . "- Local food suggestions\n"
             . "- Transport tips\n"
             . "- Estimated costs per day (show in local currency and USD if needed)\n"
             . "- Safety and cultural etiquette notes\n"
             . "**IMPORTANT: At the very end, provide a 'Total Estimated Budget' that sums up accommodation, food, transport, and activities for the entire trip.**\n"
             . "Make it practical, engaging, and well-structured for travelers.";
    }
}