<?php

namespace App\Console\Commands;

use App\Models\SosAlert;
use App\Models\User;
use App\Services\RescueDispatchService;
use App\Notifications\AdminAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EscalateSosAlerts extends Command
{
    protected $signature = 'sos:escalate';
    protected $description = 'Auto-escalate SOS alerts unacknowledged for too long';

    public function handle(): int
    {
        $adminMin  = (int) config('sos.escalation.admin_after_minutes', 5);
        $rescueMin = (int) config('sos.escalation.rescue_after_minutes', 10);
        $escalated = 0;

        // Stage 1: Notify admins after X minutes
        $needsAdminNotify = SosAlert::whereIn('status', ['pending', 'sent'])
            ->whereNull('admin_notified_at')
            ->where('created_at', '<=', now()->subMinutes($adminMin))
            ->get();

        foreach ($needsAdminNotify as $sos) {
            $this->notifyAdmins($sos, $adminMin);
            $sos->update(['admin_notified_at' => now()]);
            $sos->logAction(
                'admin_notified', null,
                $sos->status, $sos->status,
                "Auto-escalated to admin after {$adminMin}min (no acknowledgment)"
            );
            $escalated++;
        }

        // Stage 2: Rescue escalation (broadcast to nearest providers)
        $needsRescue = SosAlert::whereIn('status', ['pending', 'sent'])
            ->whereNotNull('admin_notified_at')
            ->whereNull('auto_escalated_at')
            ->where('created_at', '<=', now()->subMinutes($rescueMin))
            ->get();

        if ($needsRescue->isNotEmpty()) {
            // ✅ RescueDispatchService लाई इन्जेक्ट गर्ने
            $dispatchService = app(RescueDispatchService::class);

            foreach ($needsRescue as $sos) {
                // Broadcast गर्ने (यसले नजिकैको Online Provider खोज्छ र खबर पठाउँछ)
                $notifiedCount = $dispatchService->broadcast($sos);

                if ($notifiedCount > 0) {
                    // यदि सफल भयो भने
                    $sos->update(['auto_escalated_at' => now()]);
                    
                    // Status 'sent' मा अपडेट हुन्छ (किनकि broadcast() ले यो काम गर्छ)
                    $sos->logAction(
                        'auto_escalated', null,
                        'pending', 'sent', 
                        "Auto-escalated to {$notifiedCount} rescue provider(s) after {$rescueMin}min"
                    );
                    
                    $this->info("Escalated SOS #{$sos->id} to {$notifiedCount} rescue provider(s).");
                    $escalated++;
                } else {
                    // यदि कोही पनि Online भएनन् भने
                    $this->warn("No rescue providers available for SOS #{$sos->id}");
                    Log::warning('SOS auto-escalated to rescue, but no providers were online/available', ['sos_id' => $sos->id]);
                }
            }
        }

        $this->info("Total escalated SOS alerts: {$escalated}");
        return self::SUCCESS;
    }

    private function notifyAdmins(SosAlert $sos, int $minutes): void
    {
        try {
            $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
            $ageMin = (int) $sos->created_at->diffInMinutes(now());
            foreach ($admins as $admin) {
                $admin->notify(new AdminAlertNotification(
                    '🚨 SOS NOT ACKNOWLEDGED',
                    "SOS #{$sos->id} unacknowledged for {$ageMin} min (threshold: {$minutes} min)",
                    'sos_escalation',
                    url('/admin/dashboard'),
                    $sos->id
                ));
            }
        } catch (\Throwable $e) {
            Log::error('SOS admin escalation failed', ['sos_id' => $sos->id, 'error' => $e->getMessage()]);
        }
    }
}