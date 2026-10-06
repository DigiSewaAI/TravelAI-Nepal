<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SosAlert extends Model
{
    use HasFactory;

    protected $table = 'sos_alerts';

    protected $fillable = [
        // New (user-based, Option 2)
        'traveler_id',
        'provider_id',
        'status',
        'sent_at',
        'resolved_at',

        // Location
        'latitude',
        'longitude',
        'message',

        // Legacy (kept nullable for backward compat)
        'trekker_id',
        'booking_id',
        'is_resolved',
    ];

    protected $casts = [
        'latitude'    => 'decimal:7',
        'longitude'   => 'decimal:7',
        'is_resolved' => 'boolean',
        'sent_at'     => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // ── Relations ───────────────────────────────────────────

    public function traveler()
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    // ── Scopes ──────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'sent']);
    }
}