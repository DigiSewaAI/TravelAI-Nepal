<?php

namespace App\Services\Safety\Contracts;

interface SafetySourceAdapterInterface
{
    /**
     * Fetch raw content from the source.
     */
    public function fetch(string $url): ?string;

    /**
     * Parse raw content into normalized incident candidates.
     * Returns array of [title, description, type, severity?, location?, published_at?]
     */
    public function parse(string $raw): array;

    /**
     * Normalize candidate into a consistent array structure.
     */
    public function normalize(array $candidate): array;
}