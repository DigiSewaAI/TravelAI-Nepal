<?php

namespace App\Jobs\Safety;

use App\Models\TravelSafetyIncident;
use App\Models\SafetyAuditLog;
use App\Services\Safety\LocationMatchingService;
use App\Services\Safety\RiskScoringService;
use App\Services\Safety\SafetyStatusService;
use App\Jobs\Safety\UpdateSafetyStatusesJob;
use App\Jobs\Safety\SendSafetyAlertsJob;
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
        RiskScoringService $riskService,
        SafetyStatusService $statusService
    ): void {
        $incident = TravelSafetyIncident::find($this->incidentId);
        if (!$incident) {
            return;
        }

        Log::info('Processing incident detection', ['incident_id' => $incident->id]);

        try {
            // 1. Match location
            $matches = $matchingService->matchIncident($incident);

            // 2. If matches found, update status and confidence
            if (!empty($matches)) {
                $incident->status = 'verified';
                $incident->confidence_score = min(0.9, $incident->confidence_score + 0.2);
                $incident->last_verified_at = now();
                $incident->save();

                Log::info('Incident verified with location matches', [
                    'incident_id' => $incident->id,
                    'matches' => count($matches),
                ]);

                // Calculate initial risk scores for matched entities
                foreach ($matches as $match) {
                    $entity = $match['entity'];
                    $result = $riskService->calculateScore($incident, $entity);
                    
                    SafetyAuditLog::create([
                        'incident_id' => $incident->id,
                        'action' => 'risk_scored',
                        'old_values' => [],
                        'new_values' => [
                            'entity_type' => get_class($entity),
                            'entity_id' => $entity->id,
                            'score' => $result['score'],
                            'status' => $result['status'],
                            'factors' => $result['factors'],
                        ],
                        'reason' => 'Initial risk scoring after location match',
                    ]);
                }

                // 3. Dispatch safety status update job
                try {
                    UpdateSafetyStatusesJob::dispatch();
                } catch (\Exception $e) {
                    Log::warning('Safety status update job dispatch failed', [
                        'error' => $e->getMessage()
                    ]);
                }

                // 4. Dispatch alert job for this incident
                try {
                    SendSafetyAlertsJob::dispatch($incident->id);
                } catch (\Exception $e) {
                    Log::warning('Send safety alerts job dispatch failed', [
                        'error' => $e->getMessage()
                    ]);
                }

            } else {
                $incident->status = 'under_review';
                $incident->save();

                Log::info('Incident under review (no location matches)', [
                    'incident_id' => $incident->id,
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