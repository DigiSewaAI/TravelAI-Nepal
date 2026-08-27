<?php

namespace App\Services\Passport;

use App\Models\Booking;
use App\Models\Waypoint;
use Illuminate\Support\Facades\Log;

class QrSecurityService
{
    /**
     * Validate a scan request (token + booking + optional waypoint).
     * Returns array with 'valid' (bool) and 'message' (string).
     */
    public function validateScanRequest(Booking $booking, array $data): array
    {
        $token = $data['token'] ?? null;
        $waypointName = $data['checkpoint'] ?? null;
        $waypoint = null;

        // If waypoint name provided, try to find it
        if ($waypointName) {
            $waypoint = Waypoint::whereRaw('LOWER(name) = ?', [strtolower($waypointName)])
                                ->orWhereRaw('LOWER(slug) = ?', [strtolower($waypointName)])
                                ->first();
        }

        // Case 1: Legacy QR without token (backward compatibility)
        if (empty($token)) {
            Log::info('🔓 Legacy QR scan (no token) - allowed but pending verification', [
                'booking_id' => $booking->id,
                'checkpoint' => $waypointName,
            ]);
            return [
                'valid' => true,
                'waypoint' => $waypoint,
                'is_legacy' => true,
                'message' => 'Legacy QR scan accepted (pending verification).',
            ];
        }

        // Case 2: Check if booking has a token stored
        if (empty($booking->qr_token)) {
            Log::warning('⚠️ QR token missing in booking but client sent token', [
                'booking_id' => $booking->id,
            ]);
            return [
                'valid' => false,
                'waypoint' => null,
                'is_legacy' => false,
                'message' => 'Invalid QR token.',
            ];
        }

        // Case 3: Validate token
        $isValid = $booking->isValidQrToken($token, $waypoint?->id);

        if (!$isValid) {
            Log::warning('❌ Invalid QR token', [
                'booking_id' => $booking->id,
                'provided_token' => substr($token, 0, 10) . '...',
                'stored_token' => substr($booking->qr_token, 0, 10) . '...',
                'waypoint_id' => $waypoint?->id,
            ]);
            return [
                'valid' => false,
                'waypoint' => null,
                'is_legacy' => false,
                'message' => 'Invalid or expired QR token.',
            ];
        }

        // Check expiry
        if ($booking->isQrTokenExpired()) {
            Log::warning('⏰ QR token expired', [
                'booking_id' => $booking->id,
                'expires_at' => $booking->qr_token_expires_at,
            ]);
            return [
                'valid' => false,
                'waypoint' => null,
                'is_legacy' => false,
                'message' => 'QR token has expired.',
            ];
        }

        // All good
        Log::info('✅ QR token validated successfully', [
            'booking_id' => $booking->id,
            'waypoint_id' => $waypoint?->id,
        ]);

        return [
            'valid' => true,
            'waypoint' => $waypoint,
            'is_legacy' => false,
            'message' => 'QR token validated successfully.',
        ];
    }

    /**
     * Generate a secure QR URL for a booking.
     */
    public function generateSecureQrUrl(Booking $booking, ?int $waypointId = null): string
    {
        // Generate token for this booking and optional waypoint
        $token = $booking->generateQrToken($waypointId);
        
        // Store token in booking (if not already stored or if regenerating)
        if (empty($booking->qr_token) || $booking->qr_token !== $token) {
            $booking->qr_token = $token;
            $booking->qr_token_expires_at = now()->addDays(30);
            $booking->save();
        }

        // Build URL: /scan/{booking_id}?token={token}
        return route('scan.checkin', ['booking' => $booking->id]) . '?token=' . $token;
    }
}