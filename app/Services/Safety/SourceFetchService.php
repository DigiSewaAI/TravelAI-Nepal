<?php

namespace App\Services\Safety;

use App\Models\SafetySource;
use App\Services\Safety\SourceAdapterFactory;
use Illuminate\Support\Facades\Log;

class SourceFetchService
{
    public function fetch(SafetySource $source): array
    {
        $adapter = SourceAdapterFactory::make($source->type);
        $raw = $adapter->fetch($source->feed_url ?? $source->base_url);
        if (!$raw) {
            Log::warning('Source fetch returned no data', ['source_id' => $source->id]);
            return [];
        }
        $parsed = $adapter->parse($raw);
        $normalized = array_map([$adapter, 'normalize'], $parsed);
        return $normalized;
    }
}