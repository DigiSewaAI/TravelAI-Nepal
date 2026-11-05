<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BookingClaimAttempt extends Model
{
    protected $fillable = [
        'claimable_type', 'claimable_id', 'traveler_id',
        'contact_method', 'contact_value', 'otp_hash',
        'otp_expires_at', 'otp_attempts', 'otp_sent_at',
        'status', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
        'otp_sent_at' => 'datetime',
    ];

    public function claimable(): MorphTo
    {
        return $this->morphTo();
    }

    public function traveler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'pending'
            && $this->otp_expires_at
            && $this->otp_expires_at->isFuture();
    }
}