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
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'message'   => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        // Find most recent active booking (optional — for provider notification)
        $activeBooking = Booking::where('traveler_id', $user->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->latest()
            ->first();

        // Resolve provider through booking → service → provider
        $providerId = null;
        if ($activeBooking && $activeBooking->service) {
            $providerId = $activeBooking->service->provider_id ?? null;
        }

        // Create SOS alert
        $sos = SosAlert::create([
            'traveler_id' => $user->id,
            'provider_id' => $providerId,
            'booking_id'  => $activeBooking?->id,
            'latitude'    => $validated['latitude'],
            'longitude'   => $validated['longitude'],
            'message'     => $validated['message'] ?? null,
            'status'      => 'pending',
        ]);

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

        return response()->json([
            'success' => true,
            'message' => 'SOS alert sent. Help is on the way.',
            'sos_id'  => $sos->id,
        ], 201);
    }
}