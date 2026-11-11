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
            ->filter(function ($rp) use ($sos) {
                // 1. Verify provider
                if (!$rp->provider || $rp->provider->verification_status !== 'verified') {
                    return false;
                }

                // 2. If base coordinates are set, calculate distance
                if ($rp->base_lat && $rp->base_lng) {
                    $distance = $this->calculateDistance($rp->base_lat, $rp->base_lng, $sos->latitude, $sos->longitude);
                    $radius = $rp->coverage_radius_km ?? 50;
                    
                    if ($distance > $radius) {
                        Log::info("Provider {$rp->provider->name} skipped. Distance {$distance}km > Radius {$radius}km");
                        return false; // Too far
                    }
                }

                // 3. Fallback for legacy providers without coordinates (Safety first)
                return true;
            });
    }

    /**
     * Haversine formula to calculate distance in kilometers.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
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