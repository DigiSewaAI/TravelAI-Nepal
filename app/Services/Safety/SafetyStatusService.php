<?php

namespace App\Services\Safety;

use App\Models\TravelSafetyIncident;
use Illuminate\Support\Facades\Log;

class SafetyStatusService
{
    protected $riskService;

    public function __construct(RiskScoringService $riskService)
    {
        $this->riskService = $riskService;
    }

    /**
     * Compute aggregated safety status for an entity.
     * Returns status string and optionally the highest risk incident.
     */
    public function getStatusForEntity($entity): array
    {
        // Get active incidents linked to this entity
        $incidents = $entity->safetyIncidents()->where('status', 'active')->get();

        if ($incidents->isEmpty()) {
            return ['status' => 'unknown', 'incident' => null, 'score' => 0];
        }

        $scores = [];
        $highestScore = 0;
        $highestIncident = null;

        foreach ($incidents as $incident) {
            $score = $this->riskService->calculateScore($incident, $entity);
            $scores[] = $score;
            if ($score > $highestScore) {
                $highestScore = $score;
                $highestIncident = $incident;
            }
        }

        $status = $this->riskService->scoreToStatus($highestScore);

        return [
            'status' => $status,
            'incident' => $highestIncident,
            'score' => $highestScore,
            'all_scores' => $scores,
        ];
    }

    /**
     * Update the cached safety_status column for an entity.
     */
    public function refreshEntityStatus($entity): void
    {
        $result = $this->getStatusForEntity($entity);
        $entity->safety_status = $result['status'];
        $entity->safety_updated_at = now();
        $entity->save();

        Log::info('Safety status refreshed', [
            'entity_type' => get_class($entity),
            'entity_id' => $entity->id,
            'status' => $result['status'],
        ]);
    }
}