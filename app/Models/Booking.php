<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'trekker_id',
        'trek_id',
        'booking_date',
        'start_date',
        'status',
        'qr_code',
        'invoice_url',
        'traveler_id',
        'service_id',
        // ✅ New fields (Phase 7)
        'qr_token',
        'qr_token_expires_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_date' => 'date',
        'qr_token_expires_at' => 'datetime',
    ];

    // ... (existing relationships remain unchanged) ...

    // =============================================
    // ✅ NEW: QR Token Methods (Phase 7)
    // =============================================

    /**
     * Generate a secure QR token for this booking.
     * Token = HMAC-SHA256(booking_id + waypoint_id + secret)
     */
    public function generateQrToken(?int $waypointId = null): string
    {
        $secret = config('app.key');
        $waypointId = $waypointId ?? 0;
        $data = $this->id . '|' . $waypointId . '|' . $this->created_at->timestamp;
        
        return hash_hmac('sha256', $data, $secret);
    }

    /**
     * Check if a given token is valid for this booking and optional waypoint.
     */
    public function isValidQrToken(string $token, ?int $waypointId = null): bool
    {
        // If token is empty, it's a legacy QR code (no token) - we allow it but mark as pending.
        if (empty($this->qr_token)) {
            return true; // Backward compatibility: allow legacy QR
        }

        // Check if token matches
        if ($this->qr_token !== $token) {
            return false;
        }

        // Check expiry
        if ($this->qr_token_expires_at && now()->greaterThan($this->qr_token_expires_at)) {
            return false;
        }

        // Optional: If waypoint_id provided, regenerate token and compare (for waypoint-specific token)
        if ($waypointId) {
            $expectedToken = $this->generateQrToken($waypointId);
            if ($token !== $expectedToken) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the QR token has expired.
     */
    public function isQrTokenExpired(): bool
    {
        if (!$this->qr_token_expires_at) {
            return false;
        }
        return now()->greaterThan($this->qr_token_expires_at);
    }

    /**
     * Regenerate QR token (e.g., when booking is confirmed or waypoint changes).
     */
    public function regenerateQrToken(?int $waypointId = null): void
    {
        $this->qr_token = $this->generateQrToken($waypointId);
        $this->qr_token_expires_at = now()->addDays(30); // 30 days expiry
        $this->save();
    }
}