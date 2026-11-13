<?php

namespace App\Models;

use App\Observers\ReviewObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ReviewObserver::class])]
class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'order_id',
        'user_id',
        'service_id',
        'provider_id',
        'staff_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'comment',
        'title',
        'sub_ratings',
        'photos',
        'verified_purchase',
        'provider_response',
        'provider_responded_at',
        'helpful_count',
        'flagged_count',
        'status',
        'rejection_reason',
        'edited_at',
        'original_rating',
    ];

    protected $casts = [
        'rating'                => 'integer',
        'sub_ratings'           => 'array',
        'photos'                => 'array',
        'verified_purchase'     => 'boolean',
        'provider_responded_at' => 'datetime',
        'edited_at'             => 'datetime',
        'original_rating'       => 'integer',
        'helpful_count'         => 'integer',
        'flagged_count'         => 'integer',
    ];

    // ── Relations ─────────────────────────────
    public function booking()   { return $this->belongsTo(Booking::class); }
    public function order()     { return $this->belongsTo(Order::class); }
    public function user()      { return $this->belongsTo(User::class); }
    public function service()   { return $this->belongsTo(Service::class); }
    public function provider()  { return $this->belongsTo(Provider::class); }
    public function staff()     { return $this->belongsTo(ProviderStaff::class, 'staff_id'); }

    public function reviewable()
    {
        return $this->morphTo();
    }

    // ── Helpers ──────────────────────────────
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public function canBeEdited(): bool
    {
        // 24h edit window + only if approved or pending
        if (!in_array($this->status, ['pending', 'approved'], true)) return false;
        return $this->created_at && $this->created_at->diffInHours(now()) <= 24;
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'pending';
    }

    // ── Auto-set polymorphic on create ────────
    protected static function booted(): void
    {
        static::creating(function (Review $review) {
            if (!$review->reviewable_type) {
                if ($review->booking_id) {
                    $review->reviewable_type = Booking::class;
                    $review->reviewable_id   = $review->booking_id;
                } elseif ($review->order_id) {
                    $review->reviewable_type = Order::class;
                    $review->reviewable_id   = $review->order_id;
                }
            }
        });
    }
}