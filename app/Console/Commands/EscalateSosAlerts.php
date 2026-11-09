<?php

namespace App\Console\Commands;

use App\Models\SosAlert;
use App\Models\User;
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

        // Stage 2: Rescue escalation placeholder (after Y minutes)
        $needsRescue = SosAlert::whereIn('status', ['pending', 'sent'])
            ->whereNotNull('admin_notified_at')
            ->whereNull('auto_escalated_at')
            ->where('created_at', '<=', now()->subMinutes($rescueMin))
            ->get();

        foreach ($needsRescue as $sos) {
            $sos->update(['auto_escalated_at' => now()]);
            $sos->logAction(
                'auto_escalated', null,
                $sos->status, $sos->status,
                "Auto-escalated to rescue after {$rescueMin}min"
            );
            // TODO: When Rescue Services are added, dispatch to nearest rescue providers here
            Log::warning('SOS auto-escalated to rescue (Rescue Service not yet implemented)', ['sos_id' => $sos->id]);
            $escalated++;
        }

        $this->info("Escalated {$escalated} SOS alert(s).");
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