<?php

namespace App\Services\Safety\Adapters;

use App\Services\Safety\Contracts\SafetySourceAdapterInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class HtmlScraperAdapter implements SafetySourceAdapterInterface
{
    public function fetch(string $url): ?string
    {
        try {
            $response = Http::timeout(30)->retry(3, 100)->get($url);
            if ($response->successful()) {
                return $response->body();
            }
            Log::warning('HTML fetch failed', ['url' => $url, 'status' => $response->status()]);
            return null;
        } catch (\Exception $e) {
            Log::error('HTML fetch exception', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function parse(string $raw): array
    {
        $candidates = [];
        try {
            $crawler = new Crawler($raw);
            // यो generic छ – तपाईंले specific sources को लागि CSS selectors दिनुपर्छ
            // अहिलेको लागि हामी article elements खोज्छौं
            $articles = $crawler->filter('article, .post, .news-item, .story');
            if ($articles->count() === 0) {
                // fallback: सबै headings + paragraphs
                $headings = $crawler->filter('h1, h2, h3');
                foreach ($headings as $heading) {
                    $text = trim($heading->nodeValue);
                    if (strlen($text) > 10) {
                        $candidates[] = ['title' => $text, 'description' => ''];
                    }
                }
            } else {
                foreach ($articles as $article) {
                    $crawlerArticle = new Crawler($article);
                    $title = $crawlerArticle->filter('h1, h2, h3')->first()->text() ?? '';
                    $desc = $crawlerArticle->filter('p')->first()->text() ?? '';
                    if (empty($title) && strlen($desc) > 20) {
                        $title = substr($desc, 0, 80);
                    }
                    if (!empty($title)) {
                        $candidates[] = ['title' => trim($title), 'description' => trim($desc)];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('HTML parse error', ['error' => $e->getMessage()]);
        }
        return $candidates;
    }

    public function normalize(array $candidate): array
    {
        return [
            'title' => trim($candidate['title'] ?? ''),
            'description' => trim($candidate['description'] ?? ''),
            'source_url' => '',
            'published_at' => null,
        ];
    }
}