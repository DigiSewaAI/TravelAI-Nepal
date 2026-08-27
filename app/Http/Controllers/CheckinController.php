<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\QrScan;
use App\Models\Waypoint;
use App\Services\Passport\CheckinVerificationService;
use App\Services\Passport\QrSecurityService;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    /**
     * Show the check-in page for a booking (scan QR code).
     */
    public function show(Booking $booking, Request $request)
    {
        $qrSecurityService = app(QrSecurityService::class);

        // Validate token (if provided)
        $validation = $qrSecurityService->validateScanRequest($booking, [
            'token' => $request->query('token'),
            'checkpoint' => null, // Not needed for the page itself
        ]);

        // If invalid token, show error view
        if (!$validation['valid'] && !$validation['is_legacy']) {
            return view('checkin.error', [
                'message' => $validation['message'],
                'booking' => $booking,
            ]);
        }

        return view('checkin.scan', compact('booking'));
    }

    /**
     * Record a check-in at a checkpoint.
     */
    public function checkin(Request $request, Booking $booking)
    {
        $request->validate([
            'checkpoint' => 'required|string|max:255',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
            'token'      => 'nullable|string', // token from QR
        ]);

        // Step 1: Validate QR token
        $qrSecurityService = app(QrSecurityService::class);
        $validation = $qrSecurityService->validateScanRequest($booking, $request->all());

        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['message'],
            ], 403);
        }

        // Step 2: Find waypoint (if not already provided by validation)
        $waypoint = $validation['waypoint'] ?? null;
        if (!$waypoint && $request->checkpoint) {
            $waypoint = Waypoint::whereRaw('LOWER(name) = ?', [strtolower($request->checkpoint)])
                                ->orWhereRaw('LOWER(slug) = ?', [strtolower($request->checkpoint)])
                                ->first();
        }

        // Step 3: Create scan record
        $scan = QrScan::create([
            'booking_id'      => $booking->id,
            'checkpoint_name' => $request->checkpoint,
            'scanned_at'      => now(),
            'latitude'        => $request->latitude,
            'longitude'       => $request->longitude,
            'waypoint_id'     => $waypoint?->id,
            'verification_status' => $validation['is_legacy'] ? 'pending' : 'pending',
        ]);

        // Step 4: Process verification (duplicate detection & GPS auto-verify)
        try {
            $verificationService = app(CheckinVerificationService::class);
            $verificationService->processNewScan($scan);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Check-in verification failed: ' . $e->getMessage(), [
                'scan_id' => $scan->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-in recorded successfully.',
            'matched_waypoint' => $waypoint?->id ? true : false,
            'verification_status' => $scan->fresh()->verification_status,
            'is_legacy' => $validation['is_legacy'],
        ]);
    }
}