<?php

namespace App\Services\Passport;

use App\Models\QrScan;
use App\Models\User;
use App\Models\Waypoint;
use Illuminate\Support\Facades\Log;

class CheckinVerificationService
{
    /**
     * Detect if a scan is a duplicate (same booking + same waypoint within a time window).
     * Returns the original scan if duplicate found, else null.
     */
    public function detectDuplicate(QrScan $scan): ?QrScan
    {
        // Only check if waypoint_id is set
        if (!$scan->waypoint_id) {
            return null;
        }

        // Look for existing scans with same booking and same waypoint,
        // excluding itself (if it exists already with ID)
        $query = QrScan::where('booking_id', $scan->booking_id)
                       ->where('waypoint_id', $scan->waypoint_id)
                       ->where('id', '!=', $scan->id ?? 0)
                       ->orderBy('scanned_at', 'asc');

        // Optional: time window (e.g., within 24 hours)
        // If we want to allow repeated visits after a certain period, we can add:
        // ->where('scanned_at', '>=', now()->subHours(24))

        $original = $query->first();

        return $original;
    }

    /**
     * Mark a scan as duplicate of another scan.
     */
    public function markAsDuplicate(QrScan $scan, QrScan $original): void
    {
        $scan->duplicate_of = $original->id;
        $scan->verification_status = 'pending'; // keep pending, but it's a duplicate
        $scan->save();

        Log::info("🔁 Scan marked as duplicate", [
            'scan_id' => $scan->id,
            'original_id' => $original->id,
            'booking_id' => $scan->booking_id,
            'waypoint_id' => $scan->waypoint_id,
        ]);
    }

    /**
     * Auto-verify a scan based on GPS proximity to waypoint.
     * Returns true if verified within radius.
     */
    public function autoVerifyByGPS(QrScan $scan, int $radiusMeters = 500): bool
    {
        if (!$scan->waypoint_id) {
            return false;
        }

        $waypoint = $scan->waypoint;
        if (!$waypoint || is_null($waypoint->latitude) || is_null($waypoint->longitude)) {
            return false;
        }

        // If scan has no GPS, can't verify
        if (is_null($scan->latitude) || is_null($scan->longitude)) {
            return false;
        }

        // Calculate distance using Haversine formula
        $distance = $this->haversineDistance(
            $scan->latitude,
            $scan->longitude,
            $waypoint->latitude,
            $waypoint->longitude
        );

        if ($distance <= $radiusMeters) {
            $scan->verification_status = 'verified';
            $scan->verified_at = now();
            $scan->save();

            Log::info("✅ Scan auto-verified by GPS", [
                'scan_id' => $scan->id,
                'distance' => round($distance, 2) . 'm',
                'radius' => $radiusMeters . 'm',
            ]);

            return true;
        }

        return false;
    }

    /**
     * Manually verify a scan by admin or provider.
     */
    public function manualVerify(QrScan $scan, User $verifier): void
    {
        $scan->verification_status = 'verified';
        $scan->verified_by = $verifier->id;
        $scan->verified_at = now();
        $scan->save();

        Log::info("✅ Scan manually verified", [
            'scan_id' => $scan->id,
            'verifier_id' => $verifier->id,
            'verifier_email' => $verifier->email,
        ]);
    }

    /**
     * Reject a scan (mark as rejected).
     */
    public function rejectScan(QrScan $scan, User $verifier, ?string $reason = null): void
    {
        $scan->verification_status = 'rejected';
        $scan->verified_by = $verifier->id;
        $scan->verified_at = now();
        $scan->save();

        Log::info("❌ Scan rejected", [
            'scan_id' => $scan->id,
            'verifier_id' => $verifier->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Process a new scan: detect duplicates and attempt auto-verification.
     * This is meant to be called after a scan is created.
     */
    public function processNewScan(QrScan $scan): void
    {
        // 1. Duplicate detection
        $duplicate = $this->detectDuplicate($scan);
        if ($duplicate) {
            $this->markAsDuplicate($scan, $duplicate);
            // If it's a duplicate, we might skip auto-verification or keep pending.
            return;
        }

        // 2. Auto-verification by GPS (if waypoint and GPS available)
        $this->autoVerifyByGPS($scan, 500); // 500 meters radius
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}