<?php

namespace App\Services\Safety\Adapters;

use App\Services\Safety\Contracts\SafetySourceAdapterInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JsonApiAdapter implements SafetySourceAdapterInterface
{
    public function fetch(string $url): ?string
    {
        try {
            $response = Http::timeout(30)->retry(3, 100)->get($url);
            if ($response->successful()) {
                return $response->body();
            }
            Log::warning('JSON API fetch failed', ['url' => $url, 'status' => $response->status()]);
            return null;
        } catch (\Exception $e) {
            Log::error('JSON API fetch exception', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function parse(string $raw): array
    {
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return [];
        }
        // यो source-specific हुनेछ, तर अहिले generic array flattening गरौं
        // तपाईंले प्रत्येक source को लागि आफ्नै parser बनाउन सक्नुहुन्छ
        $items = $data['items'] ?? $data['data'] ?? [];
        if (!is_array($items)) {
            $items = [$items];
        }
        $candidates = [];
        foreach ($items as $item) {
            $candidates[] = [
                'title' => $item['title'] ?? $item['name'] ?? '',
                'description' => $item['description'] ?? $item['body'] ?? '',
                'link' => $item['url'] ?? $item['link'] ?? '',
                'published_at' => $item['published_at'] ?? $item['created_at'] ?? '',
            ];
        }
        return $candidates;
    }

    public function normalize(array $candidate): array
    {
        $publishedAt = null;
        if (!empty($candidate['published_at'])) {
            try {
                $publishedAt = date('Y-m-d H:i:s', strtotime($candidate['published_at']));
            } catch (\Exception $e) {}
        }
        return [
            'title' => trim($candidate['title'] ?? ''),
            'description' => trim($candidate['description'] ?? ''),
            'source_url' => $candidate['link'] ?? '',
            'published_at' => $publishedAt,
        ];
    }
}