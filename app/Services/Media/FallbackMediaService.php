<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class FallbackMediaService
{
    protected $pexelsApiKey;

    public function __construct()
    {
        $this->pexelsApiKey = config('services.pexels.api_key');
    }

    /**
     * Get a fallback image for a destination.
     * Returns URL of an image.
     */
    public function getImageForDestination(string $destination): ?string
    {
        $cacheKey = 'fallback_image_' . md5($destination);
        return Cache::remember($cacheKey, 86400, function () use ($destination) {
            // Try Pexels
            if ($this->pexelsApiKey) {
                $response = Http::withHeaders([
                    'Authorization' => $this->pexelsApiKey,
                ])->get('https://api.pexels.com/v1/search', [
                    'query' => $destination . ' nepal',
                    'per_page' => 1,
                ]);
                if ($response->successful() && $data = $response->json()) {
                    if (!empty($data['photos'])) {
                        return $data['photos'][0]['src']['large'];
                    }
                }
            }
            
            // Fallback to Pixabay (no key required for limited requests)
            $response = Http::get('https://pixabay.com/api/', [
                'key' => config('services.pixabay.api_key'), // optional free key
                'q' => $destination . ' nepal',
                'image_type' => 'photo',
                'per_page' => 1,
            ]);
            if ($response->successful() && $data = $response->json()) {
                if (!empty($data['hits'])) {
                    return $data['hits'][0]['largeImageURL'];
                }
            }
            
            return null;
        });
    }
}