<?php

namespace App\Services;

use App\Models\SosAlert;
use App\Models\RescueProvider;
use App\Models\RescueMission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RescueDispatchService
{
    /**
     * Broadcast an SOS to the nearest available rescue providers.
     * 
     * @param SosAlert $sos
     * @return int Number of providers notified
     */
    public function broadcast(SosAlert $sos): int
    {
        // 1. यदि SOS पहिले नै कसैले लिएको छ भने, ब्रोडकास्ट गर्नु पर्दैन
        if (in_array($sos->status, ['acknowledged', 'responding', 'resolved'])) {
            return 0;
        }

        // 2. नजिकैका Online Rescue Providers खोज्ने
        $providers = $this->findNearestProviders($sos);

        if ($providers->isEmpty()) {
            Log::warning("No available rescue providers found for SOS #{$sos->id}");
            return 0;
        }

        // 3. सबैलाई Notification पठाउने (यहाँ हामी SMS/Push/Email पठाउन सक्छौं। 
        // अहिलेलाई हामीले लग मात्र राख्नेछौं र ड्यासबोर्डमा देखिनेछ)
        foreach ($providers as $provider) {
            Log::info("SOS #{$sos->id} broadcasted to Rescue Provider: {$provider->name}");
            
            // TODO: Send SMS / Push Notification here
            // Example: \Notification::send($provider->user, new SosAlertNotification($sos));
        }

        // 4. SOS लाई 'sent' बनाउने (यदि 'pending' छ भने)
        $sos->update(['status' => 'sent']);

        return $providers->count();
    }

    /**
     * Find rescue providers based on availability.
     * (भविष्यमा हामी GPS distance हिसाब गर्न सक्छौं, अहिलेलाई जो Online छन् ती सबैलाई पठाउने)
     */
    private function findNearestProviders(SosAlert $sos)
    {
        return RescueProvider::with('provider')
            ->where('is_available', true)
            ->get()
            ->filter(function ($rp) {
                // Provider Online छ र Verified छ भने मात्र
                return $rp->provider && $rp->provider->verification_status === 'verified';
            });
    }

    /**
     * Admin (वा System) ले म्यानुअल रूपमा मिसन असाइन गर्ने
     */
    public function assignMission(SosAlert $sos, int $providerId): RescueMission
    {
        return DB::transaction(function () use ($sos, $providerId) {
            // मिसन नम्बर जेनेरेट गर्ने (RSC-2026-XXXX)
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