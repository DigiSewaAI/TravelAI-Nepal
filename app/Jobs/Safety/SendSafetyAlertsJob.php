<?php

namespace App\Jobs\Safety;

use App\Models\TravelSafetyIncident;
use App\Services\Safety\AlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSafetyAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $incidentId;

    public function __construct(?int $incidentId = null)
    {
        $this->incidentId = $incidentId;
    }

    public function handle(AlertService $alertService): void
    {
        if ($this->incidentId) {
            // Send alerts for specific incident
            $incident = TravelSafetyIncident::find($this->incidentId);
            if ($incident && in_array($incident->status, ['active', 'verified'])) {
                $alertService->checkAllTravelers($incident);
            }
        } else {
            // Send alerts for all active incidents
            $incidents = TravelSafetyIncident::whereIn('status', ['active', 'verified'])
                ->where('confidence_score', '>=', 0.4)
                ->get();

            foreach ($incidents as $incident) {
                $alertService->checkAllTravelers($incident);
            }
        }

        Log::info('Safety alerts processing completed', [
            'incident_id' => $this->incidentId ?? 'all',
        ]);
    }
}