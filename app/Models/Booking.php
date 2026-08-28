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
        'qr_token',
        'qr_token_expires_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_date' => 'date',
        'qr_token_expires_at' => 'datetime',
    ];

    // =============================================
    // RELATIONSHIPS
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
    // QR TOKEN METHODS (Phase 7)
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
}