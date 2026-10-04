<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleRfq extends Model
{
    protected $fillable = [
        'rfq_number', 'buyer_id', 'provider_id', 'product_id',
        'quantity', 'message', 'buyer_company', 'buyer_phone',
        'quoted_price', 'quoted_total', 'provider_response', 'valid_until',
        'status', 'responded_at', 'accepted_at', 'rejected_at',
    ];

    protected $casts = [
        'valid_until'  => 'date',
        'responded_at' => 'datetime',
        'accepted_at'  => 'datetime',
        'rejected_at'  => 'datetime',
        'quoted_price' => 'decimal:2',
        'quoted_total' => 'decimal:2',
        'quantity'     => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rfq) {
            if (empty($rfq->rfq_number)) {
                $year = now()->format('y');
                $last = static::where('rfq_number', 'LIKE', "RFQ-{$year}-%")
                    ->orderByDesc('rfq_number')
                    ->value('rfq_number');
                $next = $last ? ((int) substr($last, -5)) + 1 : 1;
                $rfq->rfq_number = sprintf('RFQ-%s-%05d', $year, $next);
            }
        });
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function messages()
    {
        return $this->hasMany(WholesaleRfqMessage::class, 'rfq_id')->latest('id');
    }

    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }

    public function scopeQuoted($q)
    {
        return $q->where('status', 'quoted');
    }

    public function scopeForProvider($q, $pid)
    {
        return $q->where('provider_id', $pid);
    }

    public function scopeForBuyer($q, $uid)
    {
        return $q->where('buyer_id', $uid);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isQuoted(): bool
    {
        return $this->status === 'quoted';
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast() && $this->status === 'quoted';
    }
}