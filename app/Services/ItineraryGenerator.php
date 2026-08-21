<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ItineraryGenerator
{
    public function generate(array $data): string
    {
        $apiKey = env('GROQ_API_KEY');
        
        if (!$apiKey) {
            Log::error('GROQ_API_KEY is missing in .env file');
            return "⚠️ API key not configured. Please add GROQ_API_KEY to your .env file.";
        }

        $prompt = $this->buildPrompt($data);
        
        // ✅ Model Name Update गरियो – अब `llama-3.1-8b-instant` को सट्टा `openai/gpt-oss-20b`
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'openai/gpt-oss-20b', // ✅ यहाँ बदलियो
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a professional travel planner for any destination worldwide. Provide detailed, practical itineraries with daily activities, accommodation suggestions, transport options, and local tips. Use local currency where appropriate. Always respond in English.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1500,
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'];
        }

        // 🔥 Detailed Error Logging
        $errorBody = $response->body();
        Log::error('Groq API Error: ' . $errorBody);
        
        // 🔥 User-friendly Error Message
        return "⚠️ Unable to generate itinerary at this moment. Please try again later.";
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