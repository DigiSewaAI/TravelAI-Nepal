<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ad extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'provider_id', 'title', 'description', 'image_path',
        'link_url', 'link_target', 'cta_text',
        'duration_days', 'price_paid',
        'payment_status', 'payment_proof_path',
        'payment_submitted_at', 'payment_verified_at',
        'status', 'start_date', 'end_date',
        'target_destination', 'target_category_id',
        'placement', 'sort_order',
        'impressions', 'clicks',
        'verified_by', 'verified_at', 'rejection_reason',
    ];

    protected $casts = [
        'price_paid'             => 'integer',
        'duration_days'          => 'integer',
        'sort_order'             => 'integer',
        'impressions'            => 'integer',
        'clicks'                 => 'integer',
        'payment_submitted_at'   => 'datetime',
        'payment_verified_at'    => 'datetime',
        'start_date'             => 'datetime',
        'end_date'               => 'datetime',
        'verified_at'            => 'datetime',
    ];

    const PRICING = [
        7  => 300,
        15 => 500,
        30 => 800,
    ];

    // ── Relations ──────────────────────────────────────────

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function payments()
    {
        return $this->hasMany(AdPayment::class);
    }

    public function impressionsLog()
    {
        return $this->hasMany(AdImpression::class);
    }

    public function clicksLog()
    {
        return $this->hasMany(AdClick::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ── Scopes ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopeForPlacement($query, string $placement = 'dashboard_featured')
    {
        return $query->where('placement', $placement);
    }

    // ── Helpers ────────────────────────────────────────────

    public function getCtrAttribute(): float
    {
        return $this->impressions > 0
            ? round(($this->clicks / $this->impressions) * 100, 2)
            : 0.0;
    }

    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function isActiveNow(): bool
    {
        return $this->status === 'active'
            && (!$this->start_date || $this->start_date->isPast())
            && (!$this->end_date || $this->end_date->isFuture());
    }

    public static function calculatePrice(int $days): int
    {
        return self::PRICING[$days] ?? 0;
    }
}