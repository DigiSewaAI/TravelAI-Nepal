<?php

namespace Database\Seeders;

use App\Models\SafetySource;
use Illuminate\Database\Seeder;

class SafetySourceSeeder extends Seeder
{
    public function run()
    {
        SafetySource::create([
            'name' => 'DRR Nepal RSS',
            'type' => 'rss',
            'feed_url' => 'https://example.com/feed',
            'source_category' => 'official',
            'reliability_score' => 0.9,
            'enabled' => true,
            'fetch_interval' => 15,
            'parser_type' => 'rss',
        ]);
        // add more as needed
    }
}