<?php

namespace App\Jobs\Safety;

use App\Models\TravelSafetyIncident;
use App\Services\Safety\LocationMatchingService;
use App\Services\Safety\RiskScoringService;
use App\Jobs\Safety\UpdateSafetyStatusesJob;  // ✅ IMPORTANT: Add this
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessIncidentDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $incidentId;

    public function __construct(int $incidentId)
    {
        $this->incidentId = $incidentId;
    }

    public function handle(
        LocationMatchingService $matchingService,
        RiskScoringService $riskService
    ): void {
        $incident = TravelSafetyIncident::find($this->incidentId);
        if (!$incident) {
            return;
        }

        try {
            // 1. Match location
            $matches = $matchingService->matchIncident($incident);

            // 2. If matches found, update status and confidence
            if (!empty($matches)) {
                $incident->status = 'verified';
                $incident->confidence_score = min(0.9, $incident->confidence_score + 0.2);
                $incident->save();

                Log::info('Incident verified with matches', [
                    'incident_id' => $incident->id,
                    'match_count' => count($matches)
                ]);

                // 3. Dispatch safety status update job
                try {
                    UpdateSafetyStatusesJob::dispatch();
                } catch (\Exception $e) {
                    Log::warning('Safety status update job dispatch failed', [
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $incident->status = 'under_review';
                $incident->save();
                Log::info('Incident under review - no matches found', [
                    'incident_id' => $incident->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Incident detection processing failed', [
                'incident_id' => $incident->id,
                'error' => $e->getMessage()
            ]);
            // Set to under_review on error
            $incident->status = 'under_review';
            $incident->save();
        }
    }
}