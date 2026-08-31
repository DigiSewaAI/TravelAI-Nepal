<?php

namespace App\Services\Safety;

use App\Services\Safety\Contracts\SafetySourceAdapterInterface;
use App\Services\Safety\Adapters\RssAdapter;
use App\Services\Safety\Adapters\JsonApiAdapter;
use App\Services\Safety\Adapters\HtmlScraperAdapter;
use InvalidArgumentException;

class SourceAdapterFactory
{
    public static function make(string $type): SafetySourceAdapterInterface
    {
        return match ($type) {
            'rss' => new RssAdapter(),
            'json' => new JsonApiAdapter(),
            'html' => new HtmlScraperAdapter(),
            default => throw new InvalidArgumentException("Unsupported adapter type: {$type}"),
        };
    }
}