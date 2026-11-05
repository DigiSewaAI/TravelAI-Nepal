<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingClaimEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'booking_claim_id', 'booking_claim_attempt_id', 'traveler_id',
        'event_type', 'claimable_type', 'claimable_id',
        'ip_address', 'user_agent', 'metadata', 'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];
}