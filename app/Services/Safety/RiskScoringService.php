<?php

namespace App\Services\Safety;

use App\Models\TravelSafetyIncident;
use Illuminate\Support\Facades\Log;

class RiskScoringService
{
    protected $weights;
    protected $thresholds;

    public function __construct()
    {
        $this->weights = config('safety.risk_weights', []);
        $this->thresholds = config('safety.risk_thresholds', []);
    }

    /**
     * Calculate risk score for a given incident and affectable entity.
     * Returns score 0-100.
     */
    public function calculateScore(TravelSafetyIncident $incident, $affectable): float
    {
        $score = 0;

        // 1. Severity (30%)
        $severityValue = config('safety.severity_map')[$incident->severity] ?? 0;
        $severityNorm = $severityValue / 4; // max 4
        $score += $this->weights['severity'] * $severityNorm;

        // 2. Source confidence (20%)
        $avgSourceReliability = $incident->sources()->avg('source_reliability') ?? 0.5;
        $score += $this->weights['source_confidence'] * $avgSourceReliability;

        // 3. Distance (15%) – inverse: closer = higher risk
        $distance = $this->getDistance($incident, $affectable);
        $maxRadius = config('safety.matching.max_radius', 50000);
        $distanceNorm = $distance === null ? 0 : max(0, 1 - ($distance / $maxRadius));
        $score += $this->weights['distance'] * $distanceNorm;

        // 4. Official confirmation (15%)
        $official = $incident->official_confirmation ? 1 : 0;
        $score += $this->weights['official_confirmation'] * $official;

        // 5. Travel impact (10%)
        $impactValue = config('safety.travel_impact_map')[$incident->travel_impact] ?? 0;
        $impactNorm = $impactValue / 3; // max 3
        $score += $this->weights['travel_impact'] * $impactNorm;

        // 6. Recency (10%) – newer = higher
        $hoursSinceReport = $incident->reported_at ? now()->diffInHours($incident->reported_at) : 72;
        $recencyNorm = max(0, 1 - ($hoursSinceReport / 72)); // 72 hours max
        $score += $this->weights['recency'] * $recencyNorm;

        // Clamp to 0-100
        return min(100, max(0, $score));
    }

    /**
     * Get distance between incident and affectable entity (in meters).
     * Returns null if coordinates missing.
     */
    protected function getDistance(TravelSafetyIncident $incident, $affectable): ?float
    {
        if (!$incident->latitude || !$incident->longitude) {
            return null;
        }

        // Try to get coordinates from affectable (it should have latitude/longitude)
        $lat = null;
        $lng = null;
        if (method_exists($affectable, 'getLatitudeAttribute')) {
            $lat = $affectable->getLatitudeAttribute();
            $lng = $affectable->getLongitudeAttribute();
        } elseif (property_exists($affectable, 'latitude')) {
            $lat = $affectable->latitude;
            $lng = $affectable->longitude;
        } else {
            // fallback: try to get from location relation if exists
            if (method_exists($affectable, 'location') && $affectable->location) {
                $loc = $affectable->location;
                $lat = $loc->latitude ?? null;
                $lng = $loc->longitude ?? null;
            }
        }

        if ($lat === null || $lng === null) {
            return null;
        }

        // Haversine formula
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat - $incident->latitude);
        $dLon = deg2rad($lng - $incident->longitude);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($incident->latitude)) * cos(deg2rad($lat)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Map score to status.
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
}