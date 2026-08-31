<?php

namespace App\Models\Traits;

use App\Models\TravelSafetyIncident;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait HasSafetyStatus
{
    /**
     * Get all safety incidents affecting this entity.
     * ✅ Fixed: specify pivot table and foreign keys.
     */
    public function safetyIncidents()
    {
        return $this->morphToMany(
            TravelSafetyIncident::class,
            'affectable',
            'incident_affectables',
            'affectable_id',   // foreign key for this model (the affectable entity)
            'incident_id'      // related key for the incident
        )->withPivot('distance', 'match_type', 'confidence', 'metadata')
         ->withTimestamps()
         ->where('status', 'active'); // only active incidents
    }

    /**
     * Get the computed safety status (cached).
     */
    public function getSafetyStatusAttribute()
    {
        // Return cached column if available and not stale
        if ($this->safety_updated_at && $this->safety_updated_at->gt(now()->subMinutes(15))) {
            return $this->safety_status;
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
            return 'unknown';
        }

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

        // Cache the computed status in the database columns
        $this->update([
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