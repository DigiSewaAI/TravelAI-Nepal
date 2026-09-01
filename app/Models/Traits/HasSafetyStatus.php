<?php

namespace App\Models\Traits;

use App\Models\TravelSafetyIncident;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;

trait HasSafetyStatus
{
    public function safetyIncidents(): MorphToMany
    {
        return $this->morphToMany(
            TravelSafetyIncident::class,
            'affectable',
            'incident_affectables',
            'affectable_id',
            'incident_id',
            'id',
            'id'
        )->withPivot('distance', 'match_type', 'confidence', 'metadata')
         ->withTimestamps()
         ->whereIn('status', ['active', 'verified', 'under_review']);
    }

    /**
     * Accessor for safety_status – uses raw attributes to avoid recursion.
     */
    public function getSafetyStatusAttribute(): string
    {
        // ✅ Use raw attributes – no recursion
        $status = $this->attributes['safety_status'] ?? null;
        $updated = $this->attributes['safety_updated_at'] ?? null;

        // If manually set (non-unknown), return it without any DB operation
        if ($status && $status !== 'unknown') {
            return $status;
        }

        // If status is null or unknown, compute fresh (but only if not cached)
        if ($updated && \Carbon\Carbon::parse($updated)->gt(now()->subMinutes(15))) {
            return $status ?? 'unknown';
        }

        // Compute fresh
        return $this->computeSafetyStatus();
    }

    public function computeSafetyStatus(): string
    {
        // ✅ Use raw attributes to avoid recursion
        $currentStatus = $this->attributes['safety_status'] ?? null;

        // If already manually set (non-unknown), skip computation
        if ($currentStatus && $currentStatus !== 'unknown') {
            return $currentStatus;
        }

        // Direct DB query for incident severities
        $severities = DB::table('incident_affectables')
            ->join('travel_safety_incidents', 'incident_affectables.incident_id', '=', 'travel_safety_incidents.id')
            ->where('incident_affectables.affectable_id', $this->id)
            ->where('incident_affectables.affectable_type', 'App\\Models\\Waypoint') // ✅ exact DB format
            ->whereIn('travel_safety_incidents.status', ['active', 'verified', 'under_review'])
            ->whereNull('travel_safety_incidents.deleted_at')
            ->pluck('travel_safety_incidents.severity')
            ->unique()
            ->values()
            ->toArray();

        if (empty($severities)) {
            // No incidents – keep current status or set unknown
            return $currentStatus ?? 'unknown';
        }

        $priority = ['critical' => 4, 'high' => 3, 'moderate' => 2, 'low' => 1];
        $max = 0;
        foreach ($severities as $sev) {
            $max = max($max, $priority[$sev] ?? 0);
        }

        $mapping = [
            4 => 'avoid',
            3 => 'high_risk',
            2 => 'caution',
            1 => 'normal',
            0 => 'unknown',
        ];
        $computedStatus = $mapping[$max] ?? 'unknown';

        // Only update if computed is different and not 'unknown'
        if ($currentStatus !== $computedStatus && $computedStatus !== 'unknown') {
            $this->updateQuietly([
                'safety_status' => $computedStatus,
                'safety_updated_at' => now(),
            ]);
        }

        return $computedStatus;
    }

    public function refreshSafetyStatus(): void
    {
        $this->computeSafetyStatus();
    }

    public function isSafe(): bool
    {
        $status = $this->attributes['safety_status'] ?? 'unknown';
        return in_array($status, ['normal', 'unknown']);
    }
}