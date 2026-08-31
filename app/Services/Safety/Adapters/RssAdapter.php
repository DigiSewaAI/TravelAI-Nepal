<?php

namespace App\Services\Safety\Adapters;

use App\Services\Safety\Contracts\SafetySourceAdapterInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class RssAdapter implements SafetySourceAdapterInterface
{
    public function fetch(string $url): ?string
    {
        try {
            $response = Http::timeout(30)->retry(3, 100)->get($url);
            if ($response->successful()) {
                return $response->body();
            }
            Log::warning('RSS fetch failed', ['url' => $url, 'status' => $response->status()]);
            return null;
        } catch (\Exception $e) {
            Log::error('RSS fetch exception', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function parse(string $raw): array
    {
        $candidates = [];
        try {
            $xml = new SimpleXMLElement($raw);
            // Try RSS 2.0 <item>
            $items = $xml->xpath('//item');
            if (empty($items)) {
                // Try Atom <entry>
                $items = $xml->xpath('//entry');
            }
            foreach ($items as $item) {
                $title = (string) $item->title;
                $description = (string) $item->description ?: (string) $item->summary;
                $link = (string) $item->link;
                $pubDate = (string) ($item->pubDate ?? $item->published);
                $candidates[] = [
                    'title' => $title,
                    'description' => $description,
                    'link' => $link,
                    'published_at' => $pubDate,
                ];
            }
        } catch (\Exception $e) {
            Log::error('RSS parse error', ['error' => $e->getMessage()]);
        }
        return $candidates;
    }

    public function normalize(array $candidate): array
    {
        // Convert to standard structure
        $publishedAt = null;
        if (!empty($candidate['published_at'])) {
            try {
                $publishedAt = date('Y-m-d H:i:s', strtotime($candidate['published_at']));
            } catch (\Exception $e) {
                $publishedAt = null;
            }
        }
        return [
            'title' => trim($candidate['title'] ?? ''),
            'description' => trim($candidate['description'] ?? ''),
            'source_url' => $candidate['link'] ?? '',
            'published_at' => $publishedAt,
        ];
    }
}