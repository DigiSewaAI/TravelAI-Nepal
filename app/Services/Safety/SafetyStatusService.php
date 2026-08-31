<?php

namespace App\Services\Safety;

use App\Models\TravelSafetyIncident;
use App\Models\SafetyAuditLog;
use Illuminate\Support\Facades\Log;

class SafetyStatusService
{
    protected $riskService;

    public function __construct(RiskScoringService $riskService)
    {
        $this->riskService = $riskService;
    }

    /**
     * Compute aggregated safety status for an entity
     */
    public function getStatusForEntity($entity): array
    {
        // Get active incidents linked to this entity
        $incidents = $entity->safetyIncidents()
            ->where('status', 'active')
            ->get();

        if ($incidents->isEmpty()) {
            // Check for incidents in under_review or verified status
            $pending = $entity->safetyIncidents()
                ->whereIn('status', ['under_review', 'verified'])
                ->get();

            if ($pending->isNotEmpty()) {
                return [
                    'status' => 'unknown',
                    'incident' => null,
                    'score' => 0,
                    'pending_count' => $pending->count(),
                    'message' => 'Incident(s) under review',
                ];
            }

            return [
                'status' => 'unknown',
                'incident' => null,
                'score' => 0,
                'message' => 'No active incidents',
            ];
        }

        $results = [];
        $highestScore = 0;
        $highestIncident = null;

        foreach ($incidents as $incident) {
            $result = $this->riskService->calculateScore($incident, $entity);
            $results[] = $result;
            if ($result['score'] > $highestScore) {
                $highestScore = $result['score'];
                $highestIncident = $incident;
            }
        }

        $status = $this->riskService->scoreToStatus($highestScore);

        return [
            'status' => $status,
            'incident' => $highestIncident,
            'score' => $highestScore,
            'all_results' => $results,
            'status_color' => $this->riskService->getStatusColor($status),
        ];
    }

    /**
     * Update the cached safety_status column for an entity
     */
    public function refreshEntityStatus($entity): void
    {
        $result = $this->getStatusForEntity($entity);
        $oldStatus = $entity->safety_status;

        $entity->safety_status = $result['status'];
        $entity->safety_updated_at = now();
        $entity->save();

        // Log status change
        if ($oldStatus !== $result['status']) {
            $this->logStatusChange($entity, $oldStatus, $result['status'], $result);
        }

        Log::info('Safety status refreshed', [
            'entity_type' => get_class($entity),
            'entity_id' => $entity->id,
            'old_status' => $oldStatus,
            'new_status' => $result['status'],
            'score' => $result['score'] ?? 0,
        ]);
    }

    /**
     * Log status change in audit log
     */
    protected function logStatusChange($entity, $oldStatus, $newStatus, $result): void
    {
        $incident = $result['incident'] ?? null;
        if (!$incident) {
            return;
        }

        SafetyAuditLog::create([
            'incident_id' => $incident->id,
            'action' => 'status_changed',
            'old_values' => [
                'entity_type' => get_class($entity),
                'entity_id' => $entity->id,
                'status' => $oldStatus,
            ],
            'new_values' => [
                'entity_type' => get_class($entity),
                'entity_id' => $entity->id,
                'status' => $newStatus,
                'score' => $result['score'] ?? 0,
            ],
            'user_id' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'reason' => 'Automatic status update',
        ]);
    }

    /**
     * Get safety summary for dashboard
     */
    public function getDashboardSummary(): array
    {
        $incidents = TravelSafetyIncident::whereIn('status', ['active', 'verified', 'under_review'])->get();

        $summary = [
            'total' => $incidents->count(),
            'active' => 0,
            'under_review' => 0,
            'verified' => 0,
            'critical' => 0,
            'high' => 0,
            'moderate' => 0,
            'low' => 0,
        ];

        foreach ($incidents as $incident) {
            if ($incident->status === 'active') {
                $summary['active']++;
            } elseif ($incident->status === 'under_review') {
                $summary['under_review']++;
            } elseif ($incident->status === 'verified') {
                $summary['verified']++;
            }

            switch ($incident->severity) {
                case 'critical':
                    $summary['critical']++;
                    break;
                case 'high':
                    $summary['high']++;
                    break;
                case 'moderate':
                    $summary['moderate']++;
                    break;
                case 'low':
                    $summary['low']++;
                    break;
            }
        }

        return $summary;
    }
}