<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException; // for enableSharing validation

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
        'qr_token',
        'qr_token_expires_at',
        // NEW share fields
        'visibility',
        'share_token',
        'share_enabled_at',
        'share_revoked_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_date' => 'date',
        'qr_token_expires_at' => 'datetime',
        // NEW casts for share timestamps
        'share_enabled_at' => 'datetime',
        'share_revoked_at' => 'datetime',
    ];

    // =============================================
    // RELATIONSHIPS (existing)
    // =============================================

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function traveler()
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    public function qrScans()
    {
        return $this->hasMany(QrScan::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // =============================================
    // QR TOKEN METHODS (existing)
    // =============================================

    public function generateQrToken(?int $waypointId = null): string
    {
        $secret = config('app.key');
        $waypointId = $waypointId ?? 0;
        $data = $this->id . '|' . $waypointId . '|' . $this->created_at->timestamp;
        
        return hash_hmac('sha256', $data, $secret);
    }

    public function isValidQrToken(string $token, ?int $waypointId = null): bool
    {
        if (empty($this->qr_token)) {
            return true;
        }

        if ($this->qr_token !== $token) {
            return false;
        }

        if ($this->qr_token_expires_at && now()->greaterThan($this->qr_token_expires_at)) {
            return false;
        }

        if ($waypointId) {
            $expectedToken = $this->generateQrToken($waypointId);
            if ($token !== $expectedToken) {
                return false;
            }
        }

        return true;
    }

    public function isQrTokenExpired(): bool
    {
        if (!$this->qr_token_expires_at) {
            return false;
        }
        return now()->greaterThan($this->qr_token_expires_at);
    }

    public function regenerateQrToken(?int $waypointId = null): void
    {
        $this->qr_token = $this->generateQrToken($waypointId);
        $this->qr_token_expires_at = now()->addDays(30);
        $this->save();
    }

    // =============================================
    // SHARE METHODS (NEW)
    // =============================================

    /**
     * Generate a secure random share token (64 hex characters).
     */
    public function generateShareToken(): string
    {
        return bin2hex(random_bytes(32)); // 64 chars
    }

    /**
     * Check if this booking is currently shareable (token exists, enabled, not revoked).
     */
    public function isShareable(): bool
    {
        return $this->share_token !== null &&
               $this->share_enabled_at !== null &&
               $this->share_revoked_at === null;
    }

    /**
     * Check if this booking is publicly accessible (visibility = 'public' AND shareable).
     */
    public function isPublic(): bool
    {
        return $this->visibility === 'public' && $this->isShareable();
    }

    /**
     * Check if this booking can be shared via link (visibility = 'link' or 'public', AND shareable).
     */
    public function isLinkShareable(): bool
    {
        return in_array($this->visibility, ['link', 'public']) && $this->isShareable();
    }

    /**
     * Revoke the current share token (set revoked_at to now).
     */
    public function revokeShare(): void
    {
        $this->share_revoked_at = now();
        $this->save();
    }

    /**
     * Enable sharing with a given visibility.
     * Generates a new token if none exists, and clears revoked_at.
     *
     * @param string $visibility 'private', 'link', or 'public'
     * @throws InvalidArgumentException
     */
    public function enableSharing(string $visibility = 'link'): void
    {
        if (!in_array($visibility, ['private', 'link', 'public'])) {
            throw new InvalidArgumentException('Invalid visibility value. Allowed: private, link, public.');
        }

        $this->visibility = $visibility;

        if (!$this->share_token) {
            $this->share_token = $this->generateShareToken();
        }

        $this->share_enabled_at = now();
        $this->share_revoked_at = null;
        $this->save();
    }

    /**
     * Accessor: Get the full public share URL for this booking (if shareable).
     */
    public function getShareUrlAttribute(): ?string
    {
        if (!$this->isShareable()) {
            return null;
        }
        return route('public.journey.replay', ['token' => $this->share_token]);
    }

    /**
     * Find a booking by its share token, ensuring it is enabled and not revoked.
     */
    public static function findByShareToken(string $token): ?self
    {
        return static::where('share_token', $token)
                     ->whereNotNull('share_enabled_at')
                     ->whereNull('share_revoked_at')
                     ->first();
    }
}