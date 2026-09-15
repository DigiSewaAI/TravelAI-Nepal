<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeWebhookEvent extends Model
{
    protected $table = 'stripe_webhook_events';

    protected $fillable = [
        'event_id',
        'state',
        'claim_token',
        'claimed_at',
        'lease_expires_at',
        'processed_at',
    ];

    protected $casts = [
        'claimed_at'       => 'datetime',
        'lease_expires_at' => 'datetime',
        'processed_at'     => 'datetime',
    ];

    public const STATE_CLAIMED   = 'claimed';
    public const STATE_PROCESSED = 'processed';
    public const STATE_FAILED    = 'failed';

    public function isProcessed(): bool
    {
        return $this->state === self::STATE_PROCESSED;
    }

    public function isClaimed(): bool
    {
        return $this->state === self::STATE_CLAIMED;
    }

    public function isFailed(): bool
    {
        return $this->state === self::STATE_FAILED;
    }

    public function isLeaseActive(): bool
    {
        return $this->state === self::STATE_CLAIMED
            && $this->lease_expires_at !== null
            && $this->lease_expires_at->isFuture();
    }
}