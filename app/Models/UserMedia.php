<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'waypoint_id', 'booking_id', 'qr_scan_id',
        'media_type', 'file_name', 'original_path', 'optimized_path', 'thumbnail_path',
        'metadata', 'captured_at', 'is_primary', 'source'
    ];

    protected $casts = [
        'metadata' => 'array',
        'captured_at' => 'datetime',
        'is_primary' => 'boolean',
    ];

    // =============================================
    // RELATIONSHIPS (existing)
    // =============================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function waypoint()
    {
        return $this->belongsTo(Waypoint::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function qrScan()
    {
        return $this->belongsTo(QrScan::class);
    }

    // =============================================
    // HELPER METHOD (NEW - optional, but per plan)
    // =============================================

    /**
     * Check if this media belongs to a booking that is shareable via the given token.
     * Note: Actual privacy enforcement is done at the controller/middleware level,
     * but this helper can be used for convenience.
     */
    public function belongsToShareableBooking(?string $token = null): bool
    {
        if (!$this->booking) {
            return false;
        }

        $booking = $this->booking;

        // If token is provided, check if it matches the booking's share token
        if ($token !== null && $booking->share_token !== $token) {
            return false;
        }

        // Check if the booking is shareable (enabled, not revoked)
        return $booking->isShareable();
    }
}