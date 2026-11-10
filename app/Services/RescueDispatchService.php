<?php

namespace App\Services;

use App\Models\SosAlert;
use App\Models\RescueProvider;
use App\Models\RescueMission;
use App\Notifications\RescueDispatchNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RescueDispatchService
{
    public function broadcast(SosAlert $sos): int
    {
        if (in_array($sos->status, ['acknowledged', 'responding', 'resolved'])) {
            return 0;
        }

        $providers = $this->findNearestProviders($sos);

        if ($providers->isEmpty()) {
            Log::warning("No available rescue providers found for SOS #{$sos->id}");
            return 0;
        }

        foreach ($providers as $provider) {
            Log::info("SOS #{$sos->id} broadcasted to Rescue Provider: {$provider->name}");
            
            if ($provider->provider && $provider->provider->user) {
                $provider->provider->user->notify(new RescueDispatchNotification($sos));
            }
        }

        $sos->update(['status' => 'sent']);

        return $providers->count();
    }

    private function findNearestProviders(SosAlert $sos)
    {
        return RescueProvider::with('provider')
            ->where('is_available', true)
            ->get()
            ->filter(function ($rp) {
                return $rp->provider && $rp->provider->verification_status === 'verified';
            });
    }

    public function assignMission(SosAlert $sos, int $providerId): RescueMission
    {
        return DB::transaction(function () use ($sos, $providerId) {
            $missionNumber = 'RSC-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $mission = RescueMission::create([
                'mission_number' => $missionNumber,
                'sos_alert_id'   => $sos->id,
                'provider_id'    => $providerId,
                'status'         => 'dispatched',
                'pickup_lat'     => $sos->latitude,
                'pickup_lng'     => $sos->longitude,
            ]);

            $sos->update(['status' => 'responding']);

            return $mission;
        });
    }
}