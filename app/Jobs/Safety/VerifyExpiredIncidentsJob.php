<?php

namespace App\Jobs\Safety;

use App\Models\TravelSafetyIncident;
use App\Models\SafetyAuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VerifyExpiredIncidentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('Checking for expired/stale incidents');

        // Check for incidents needing verification
        $staleThreshold = config('safety.stale.verification_hours', 24);
        $expiryDays = config('safety.stale.expiry_days', 7);

        // Incidents not verified for X hours -> reduce confidence
        $staleIncidents = TravelSafetyIncident::whereIn('status', ['active', 'verified'])
            ->where('last_verified_at', '<', now()->subHours($staleThreshold))
            ->get();

        foreach ($staleIncidents as $incident) {
            $days = $incident->last_verified_at->diffInDays(now());
            $decay = config('safety.stale.confidence_decay_per_day', 0.1) * $days;
            $newConfidence = max(0, $incident->confidence_score - $decay);

            // If confidence falls below threshold, mark for review
            if ($newConfidence < 0.3) {
                $incident->status = 'under_review';
                $incident->confidence_score = $newConfidence;
                $incident->save();

                SafetyAuditLog::create([
                    'incident_id' => $incident->id,
                    'action' => 'stale_confidence_decay',
                    'old_values' => ['confidence' => $incident->confidence_score + $decay],
                    'new_values' => ['confidence' => $newConfidence],
                    'reason' => "Confidence decayed due to staleness ({$days} days without update)",
                ]);
            }

            Log::info('Incident confidence decayed', [
                'incident_id' => $incident->id,
                'days' => $days,
                'old_confidence' => $incident->confidence_score + $decay,
                'new_confidence' => $newConfidence,
            ]);
        }

        // Expire incidents older than expiry_days
        $expired = TravelSafetyIncident::whereIn('status', ['active', 'verified'])
            ->where('reported_at', '<', now()->subDays($expiryDays))
            ->get();

        foreach ($expired as $incident) {
            $incident->status = 'expired';
            $incident->expires_at = now();
            $incident->save();

            SafetyAuditLog::create([
                'incident_id' => $incident->id,
                'action' => 'expired',
                'old_values' => ['status' => 'active'],
                'new_values' => ['status' => 'expired'],
                'reason' => "Incident expired after {$expiryDays} days",
            ]);

            Log::info('Incident expired', [
                'incident_id' => $incident->id,
                'title' => $incident->title,
            ]);
        }

        // Update statuses after changes
        if ($staleIncidents->isNotEmpty() || $expired->isNotEmpty()) {
            UpdateSafetyStatusesJob::dispatch();
        }
    }
}