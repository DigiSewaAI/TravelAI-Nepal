<?php

namespace App\Jobs\Safety;

use App\Models\SafetySource;
use App\Services\Safety\SourceFetchService;
use App\Services\Safety\IncidentDetectionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

// ✅ Import ProcessIncidentDetectionJob so we can dispatch it
use App\Jobs\Safety\ProcessIncidentDetectionJob;

class FetchSafetySourcesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $timeout = 120;

    public function handle(SourceFetchService $fetchService, IncidentDetectionService $detectionService): void
    {
        $sources = SafetySource::where('enabled', true)->get();

        foreach ($sources as $source) {
            try {
                $source->last_checked_at = now();
                $items = $fetchService->fetch($source);
                if (!empty($items)) {
                    $source->last_success_at = now();
                    $source->last_error = null;
                    
                    // This will process all items and create incidents
                    // Inside detection service, it will dispatch ProcessIncidentDetectionJob
                    $detectionService->processSourceContent($source, $items);
                } else {
                    $source->last_error = 'No items fetched';
                }
                $source->save();
            } catch (\Exception $e) {
                $source->last_error = $e->getMessage();
                $source->save();
                Log::error('Source fetch failed', ['source_id' => $source->id, 'error' => $e->getMessage()]);
            }
        }
    }
}