<?php

namespace App\Models\Traits;

use App\Models\TravelSafetyIncident;
use Illuminate\Support\Facades\Log;

trait HasSafetyStatus
{
    /**
     * Get all safety incidents affecting this entity.
     */
    public function safetyIncidents()
{
    return $this->morphToMany(
        TravelSafetyIncident::class,
        'affectable',
        'incident_affectables',
        'affectable_id',
        'incident_id'
    )->withPivot('distance', 'match_type', 'confidence', 'metadata')
     ->withTimestamps()
     ->whereIn('status', ['active', 'verified', 'under_review']); // ✅ Include these
}

    /**
     * Get the computed safety status (cached).
     */
    public function getSafetyStatusAttribute()
    {
        // ✅ Check if safety_updated_at is a Carbon instance and within cache TTL
        if ($this->safety_updated_at instanceof \Carbon\Carbon) {
            if ($this->safety_updated_at->gt(now()->subMinutes(15))) {
                return $this->safety_status ?? 'unknown';
            }
        }

        // Compute fresh
        return $this->computeSafetyStatus();
    }

    /**
     * Compute safety status from linked incidents.
     */
    public function computeSafetyStatus(): string
    {
        $statuses = $this->safetyIncidents->pluck('severity')->unique();

        if ($statuses->isEmpty()) {
            $status = 'unknown';
        } else {
            // Highest severity wins (critical > high > moderate > low)
            $priority = ['critical' => 4, 'high' => 3, 'moderate' => 2, 'low' => 1];
            $max = $statuses->map(function ($s) use ($priority) {
                return $priority[$s] ?? 0;
            })->max();

            $mapping = [
                4 => 'avoid',
                3 => 'high_risk',
                2 => 'caution',
                1 => 'normal',
                0 => 'unknown',
            ];

            $status = $mapping[$max] ?? 'unknown';
        }

        // ✅ Use updateQuietly to avoid event loops and set both fields
        $this->updateQuietly([
            'safety_status' => $status,
            'safety_updated_at' => now(),
        ]);

        return $status;
    }

    /**
     * Update safety status for this entity.
     * Called after incident changes.
     */
    public function refreshSafetyStatus()
    {
        $this->computeSafetyStatus();
    }

    /**
     * Check if entity is safe (normal/unknown).
     */
    public function isSafe(): bool
    {
        return in_array($this->safety_status, ['normal', 'unknown']);
    }
}