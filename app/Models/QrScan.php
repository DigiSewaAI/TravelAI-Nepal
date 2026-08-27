<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'checkpoint_name',
        'scanned_at',
        'latitude',
        'longitude',
        // ✅ Phase 1: New Field
        'waypoint_id',
        // ✅ Phase 5: New Fields
        'verification_status',
        'duplicate_of',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'verified_at' => 'datetime', // ✅ Phase 5
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    // ✅ Existing: Belongs to Booking
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // ✅ Phase 1: Belongs to Waypoint
    public function waypoint()
    {
        return $this->belongsTo(Waypoint::class);
    }

    // ✅ Phase 5: Relationships for duplicate detection
    public function duplicate()
    {
        return $this->belongsTo(QrScan::class, 'duplicate_of');
    }

    public function duplicates()
    {
        return $this->hasMany(QrScan::class, 'duplicate_of');
    }

    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // =====================================================
    // ✅ PHASE 5 HELPERS
    // =====================================================

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }

    public function isDuplicate(): bool
    {
        return !is_null($this->duplicate_of);
    }
}