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

        // ===== Stage 0: Level-aware escalation (SOS v1.2) =====
        // critical / unclassified -> controller handles parallel dispatch; skip here
        // urgent      -> escalate if > 3 min
        // assistance  -> escalate if > 10 min
        // NULL level  -> fall through to legacy 2-stage below
        $escalated += $this->handleLevelAwareEscalation();

        // Stage 1: Notify admins after X minutes
        $needsAdminNotify = SosAlert::whereIn('status', ['pending', 'sent'])
            ->where(function ($q) {
                $q->whereNull('emergency_level')
                  ->orWhereIn('emergency_level', ['urgent', 'assistance']);
            })
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
            ->where(function ($q) {
                $q->whereNull('emergency_level')
                  ->orWhereIn('emergency_level', ['urgent', 'assistance']);
            })
            ->whereNotNull('admin_notified_at')
            ->whereNull('auto_escalated_at')
            ->where('created_at', '<=', now()->subMinutes($rescueMin))
            ->get();

        if ($needsRescue->isNotEmpty()) {
            $dispatchService = app(RescueDispatchService::class);

            foreach ($needsRescue as $sos) {
                $notifiedCount = $dispatchService->broadcast($sos);

                if ($notifiedCount > 0) {
                    $sos->update(['auto_escalated_at' => now()]);
                    $sos->logAction(
                        'auto_escalated', null,
                        'pending', 'sent',
                        "Auto-escalated to {$notifiedCount} rescue provider(s) after {$rescueMin}min"
                    );
                    $this->info("Escalated SOS #{$sos->id} to {$notifiedCount} rescue provider(s).");
                    $escalated++;
                } else {
                    $this->warn("No rescue providers available for SOS #{$sos->id}");
                    Log::warning('SOS auto-escalated to rescue, but no providers were online/available', ['sos_id' => $sos->id]);
                }
            }
        }

        $this->info("Total escalated SOS alerts: {$escalated}");
        return self::SUCCESS;
    }

    /**
     * Stage 0 - Level-aware escalation.
     * Only urgent (>3min) and assistance (>10min) handled here.
     * critical/unclassified are dispatched immediately by SosController::store().
     * NULL emergency_level falls through to legacy Stage 1/2.
     */
    private function handleLevelAwareEscalation(): int
    {
        $count = 0;
        $dispatchService = app(RescueDispatchService::class);

        // Urgent -> 3 min
        $urgent = SosAlert::whereIn('status', ['pending', 'sent'])
            ->where('emergency_level', 'urgent')
            ->whereNull('auto_escalated_at')
            ->where('created_at', '<=', now()->subMinutes(3))
            ->get();

        foreach ($urgent as $sos) {
            $n = $dispatchService->broadcast($sos);
            if ($n > 0) {
                $sos->update(['auto_escalated_at' => now()]);
                $sos->logAction('auto_escalated', null, $sos->status, $sos->status,
                    "Level-aware escalation (urgent >3min) to {$n} rescue provider(s)",
                    ['level' => 'urgent', 'providers' => $n]);
                $count++;
            } else {
                Log::warning("Level-aware urgent escalation: no providers for SOS #{$sos->id}");
            }
        }

        // Assistance -> 10 min
        $assistance = SosAlert::whereIn('status', ['pending', 'sent'])
            ->where('emergency_level', 'assistance')
            ->whereNull('auto_escalated_at')
            ->where('created_at', '<=', now()->subMinutes(10))
            ->get();

        foreach ($assistance as $sos) {
            $n = $dispatchService->broadcast($sos);
            if ($n > 0) {
                $sos->update(['auto_escalated_at' => now()]);
                $sos->logAction('auto_escalated', null, $sos->status, $sos->status,
                    "Level-aware escalation (assistance >10min) to {$n} rescue provider(s)",
                    ['level' => 'assistance', 'providers' => $n]);
                $count++;
            }
        }

        return $count;
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