<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\SosAlert;
use App\Jobs\SendSosNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SosController extends Controller
{
    /**
     * Create a new SOS alert (user-based, Option 2).
     * Auth required. Provider notification sent if active booking exists.
     *
     * SOS v1.2 - emergency_level (optional, NULL = legacy):
     * - critical/unclassified -> immediate parallel rescue dispatch
     * - urgent/assistance/NULL -> legacy 2-stage escalation (sos:escalate)
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        $validated = $request->validate([
            'latitude'        => 'required|numeric|between:-90,90',
            'longitude'       => 'required|numeric|between:-180,180',
            'message'         => 'nullable|string|max:500',
            'emergency_level' => 'nullable|in:critical,urgent,assistance,unclassified',
        ]);

        $user = Auth::user();

        // Find most recent active booking (optional - for provider notification)
        $activeBooking = Booking::where('traveler_id', $user->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->latest()
            ->first();

        // Resolve provider through booking -> service -> provider
        $providerId = null;
        if ($activeBooking && $activeBooking->service) {
            $providerId = $activeBooking->service->provider_id ?? null;
        }

        // Create SOS alert
        $sos = SosAlert::create([
            'traveler_id'     => $user->id,
            'provider_id'     => $providerId,
            'booking_id'      => $activeBooking?->id,
            'latitude'        => $validated['latitude'],
            'longitude'       => $validated['longitude'],
            'message'         => $validated['message'] ?? null,
            'status'          => 'pending',
            'emergency_level' => $validated['emergency_level'] ?? null,   // SOS v1.2
        ]);

        // Notify admins: SOS alert (safety-critical)
        try {
            $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AdminAlertNotification(
                    '🚨 SOS ALERT',
                    ($user->name ?? 'Traveler') . ' triggered SOS' . (!empty($validated['message']) ? ': ' . $validated['message'] : ''),
                    'sos',
                    url('/admin/dashboard'),
                    $sos->id
                ));
            }
        } catch (\Throwable $e) {
            \Log::warning('Admin SOS notify failed', ['sos_id' => $sos->id, 'error' => $e->getMessage()]);
        }

        // Dispatch notification (queue)
        try {
            SendSosNotification::dispatch($sos);
            $sos->update(['sent_at' => now(), 'status' => 'sent']);
        } catch (\Throwable $e) {
            Log::error('SOS dispatch failed', [
                'sos_id' => $sos->id,
                'error'  => $e->getMessage(),
            ]);
            // Alert saved even if notification fails
        }

        // ===== SOS v1.2: Parallel rescue dispatch (critical / unclassified) =====
        if (in_array($validated['emergency_level'] ?? null, ['critical', 'unclassified'], true)) {
            try {
                $notified = app(\App\Services\RescueDispatchService::class)->broadcast($sos);
                if ($notified > 0) {
                    $sos->logAction(
                        'rescue_notified', null, $sos->status, $sos->status,
                        "Immediate parallel rescue dispatch ({$validated['emergency_level']}) to {$notified} provider(s)",
                        ['level' => $validated['emergency_level'], 'providers' => $notified]
                    );
                } else {
                    \Log::warning('Parallel rescue dispatch: no providers available', ['sos_id' => $sos->id]);
                }
            } catch (\Throwable $e) {
                \Log::error('Parallel rescue dispatch failed', ['sos_id' => $sos->id, 'error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'success'         => true,
            'message'         => 'SOS alert sent. Help is on the way.',
            'sos_id'          => $sos->id,
            'emergency_level' => $sos->emergency_level,
        ], 201);
    }
}