<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'provider_id', 'product_name', 'product_type',
        'unit_price', 'quantity', 'rental_start_date', 'rental_end_date',
        'rental_days', 'rental_deposit', 'bulk_min_qty', 'line_total',
        'provider_status', 'provider_status_updated_at',
        'return_requested_at', 'return_confirmed_at', 'return_condition',
        'return_notes', 'deposit_refund_amount', 'deposit_refunded_at',
    ];

    protected $casts = [
        'rental_start_date' => 'date',
        'rental_end_date'   => 'date',
        'unit_price'        => 'decimal:2',
        'line_total'        => 'decimal:2',
        'rental_deposit'    => 'decimal:2',
        'provider_status_updated_at' => 'datetime',
        'return_requested_at'   => 'datetime',
        'return_confirmed_at'   => 'datetime',
        'deposit_refunded_at'   => 'datetime',
        'deposit_refund_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function scopeForProvider($q, $pid)
    {
        return $q->where('provider_id', $pid);
    }

    public function scopeWithProviderStatus($q, $status)
    {
        return $q->where('provider_status', $status);
    }

    // ═══════════════════════════════════════════
    // PATH-3C C2: Return flow helpers
    // ═══════════════════════════════════════════

    public function isRental(): bool
    {
        return $this->product_type === 'rental';
    }

    public function isShop(): bool
    {
        return $this->product_type === 'shop';
    }

    public function isWholesale(): bool
    {
        return $this->product_type === 'wholesale';
    }

    public function hasReturnRequest(): bool
    {
        return $this->return_requested_at !== null;
    }

    public function isReturnConfirmed(): bool
    {
        return $this->return_confirmed_at !== null;
    }

    public function isOverdue(): bool
    {
        if (!$this->isRental() || !$this->rental_end_date) return false;
        return now()->isAfter($this->rental_end_date) && !$this->isReturnConfirmed();
    }

    public function calculateDepositRefund(): float
    {
        $deposit = (float) ($this->rental_deposit ?? 0) * (int) $this->quantity;
        if ($deposit <= 0) return 0.0;

        $product = $this->product;
        $detail = $product?->rentalDetail;

        return match ($this->return_condition) {
            'good'    => $deposit,
            'damaged' => $deposit * (1 - (($detail?->damage_deposit_pct ?? 100) / 100)),
            'lost'    => $deposit * (1 - (($detail?->lost_deposit_pct ?? 100) / 100)),
            default   => 0.0,
        };
    }

    public function calculateLateFee(): float
    {
        if (!$this->isRental() || !$this->rental_end_date) return 0.0;

        $detail = $this->product?->rentalDetail;
        if (!$detail || $detail->late_fee_per_day <= 0) return 0.0;

        $returnDate = $this->return_confirmed_at ?? $this->return_requested_at ?? now();

        // Calendar-day based: start of day boundary (avoids fractional hours)
        $start = $this->rental_end_date->copy()->startOfDay();
        $end   = $returnDate->copy()->startOfDay();
        $daysOverdue = max(0, (int) $start->diffInDays($end));

        return $daysOverdue * (float) $detail->late_fee_per_day;
    }
}