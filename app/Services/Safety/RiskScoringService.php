<?php

namespace App\Services\Safety;

use App\Models\TravelSafetyIncident;
use Illuminate\Support\Facades\Log;

class RiskScoringService
{
    protected $weights;
    protected $thresholds;
    protected $severityMap;
    protected $travelImpactMap;
    protected $decayFactor;

    public function __construct()
    {
        $this->weights = config('safety.risk_weights', [
            'severity' => 30,
            'source_confidence' => 20,
            'distance' => 15,
            'official_confirmation' => 15,
            'travel_impact' => 10,
            'recency' => 10,
        ]);
        
        $this->thresholds = config('safety.risk_thresholds', [
            'normal' => 20,
            'caution' => 40,
            'high_risk' => 65,
            'avoid' => 100,
        ]);
        
        $this->severityMap = config('safety.severity_map', [
            'low' => 1,
            'moderate' => 2,
            'high' => 3,
            'critical' => 4,
        ]);
        
        $this->travelImpactMap = config('safety.travel_impact_map', [
            'none' => 0,
            'minor' => 1,
            'moderate' => 2,
            'severe' => 3,
        ]);
        
        $this->decayFactor = config('safety.stale.confidence_decay_per_day', 0.1);
    }

    /**
     * Calculate risk score for a given incident and entity
     */
    public function calculateScore(TravelSafetyIncident $incident, $entity): array
    {
        $score = 0;
        $factors = [];

        // 1. Severity (30%)
        $severityValue = $this->severityMap[$incident->severity] ?? 0;
        $severityNorm = min(1, $severityValue / 4);
        $factorScore = $this->weights['severity'] * $severityNorm;
        $score += $factorScore;
        $factors['severity'] = [
            'raw' => $severityValue,
            'normalized' => $severityNorm,
            'weighted' => $factorScore,
        ];

        // 2. Source Confidence (20%)
        $sourceReliability = $this->getAverageSourceReliability($incident);
        $factorScore = $this->weights['source_confidence'] * $sourceReliability;
        $score += $factorScore;
        $factors['source_confidence'] = [
            'raw' => $sourceReliability,
            'normalized' => $sourceReliability,
            'weighted' => $factorScore,
        ];

        // 3. Distance (15%)
        $distance = $this->calculateDistanceToEntity($incident, $entity);
        $maxRadius = config('safety.matching.max_radius', 50000);
        $distanceNorm = $distance === null ? 0 : max(0, 1 - ($distance / $maxRadius));
        $factorScore = $this->weights['distance'] * $distanceNorm;
        $score += $factorScore;
        $factors['distance'] = [
            'raw' => $distance,
            'normalized' => $distanceNorm,
            'weighted' => $factorScore,
        ];

        // 4. Official Confirmation (15%)
        $official = $incident->official_confirmation ? 1 : 0;
        $factorScore = $this->weights['official_confirmation'] * $official;
        $score += $factorScore;
        $factors['official_confirmation'] = [
            'raw' => $official,
            'normalized' => $official,
            'weighted' => $factorScore,
        ];

        // 5. Travel Impact (10%)
        $impactValue = $this->travelImpactMap[$incident->travel_impact] ?? 0;
        $impactNorm = min(1, $impactValue / 3);
        $factorScore = $this->weights['travel_impact'] * $impactNorm;
        $score += $factorScore;
        $factors['travel_impact'] = [
            'raw' => $impactValue,
            'normalized' => $impactNorm,
            'weighted' => $factorScore,
        ];

        // 6. Recency (10%)
        $hoursSinceReport = $incident->reported_at ? now()->diffInHours($incident->reported_at) : 72;
        $recencyNorm = max(0, 1 - ($hoursSinceReport / 72));
        $factorScore = $this->weights['recency'] * $recencyNorm;
        $score += $factorScore;
        $factors['recency'] = [
            'raw' => $hoursSinceReport,
            'normalized' => $recencyNorm,
            'weighted' => $factorScore,
        ];

        // 7. Stale Data Penalty
        if ($incident->last_verified_at && $incident->last_verified_at->diffInHours(now()) > 24) {
            $days = $incident->last_verified_at->diffInDays(now());
            $penalty = min(0.5, $days * $this->decayFactor);
            $score = max(0, $score - $penalty * 100);
            $factors['stale_penalty'] = [
                'raw' => $days,
                'penalty' => $penalty * 100,
            ];
        }

        // Clamp to 0-100
        $finalScore = min(100, max(0, $score));
        $status = $this->scoreToStatus($finalScore);

        return [
            'score' => $finalScore,
            'status' => $status,
            'factors' => $factors,
            'incident' => $incident,
        ];
    }

    /**
     * Get average source reliability for an incident
     */
    protected function getAverageSourceReliability(TravelSafetyIncident $incident): float
    {
        $avg = $incident->sources()->avg('source_reliability');
        return (float) ($avg ?? 0.5);
    }

    /**
     * Calculate distance between incident and entity
     */
    protected function calculateDistanceToEntity(TravelSafetyIncident $incident, $entity): ?float
    {
        if (!$incident->latitude || !$incident->longitude) {
            return null;
        }

        // Try to get entity coordinates
        $lat = null;
        $lng = null;

        if (method_exists($entity, 'getLatitudeAttribute')) {
            $lat = $entity->getLatitudeAttribute();
            $lng = $entity->getLongitudeAttribute();
        } elseif (property_exists($entity, 'latitude')) {
            $lat = $entity->latitude;
            $lng = $entity->longitude;
        } elseif (method_exists($entity, 'location') && $entity->location) {
            $loc = $entity->location;
            $lat = $loc->latitude ?? null;
            $lng = $loc->longitude ?? null;
        }

        if ($lat === null || $lng === null) {
            return null;
        }

        return $this->haversineDistance(
            $incident->latitude,
            $incident->longitude,
            (float) $lat,
            (float) $lng
        );
    }

    /**
     * Haversine distance in meters
     */
    protected function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Map score to status
     */
    public function scoreToStatus(float $score): string
    {
        if ($score <= $this->thresholds['normal']) {
            return 'normal';
        } elseif ($score <= $this->thresholds['caution']) {
            return 'caution';
        } elseif ($score <= $this->thresholds['high_risk']) {
            return 'high_risk';
        } else {
            return 'avoid';
        }
    }

    /**
     * Get severity label
     */
    public function getSeverityLabel(string $severity): string
    {
        $labels = [
            'low' => 'Low',
            'moderate' => 'Moderate',
            'high' => 'High',
            'critical' => 'Critical',
        ];
        return $labels[$severity] ?? 'Unknown';
    }

    /**
     * Get status color
     */
    public function getStatusColor(string $status): string
    {
        $colors = [
            'normal' => '🟢',
            'caution' => '🟡',
            'high_risk' => '🟠',
            'avoid' => '🔴',
            'unknown' => '⚪',
        ];
        return $colors[$status] ?? '⚪';
    }
}