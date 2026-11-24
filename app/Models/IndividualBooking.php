<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndividualBooking extends Model
{
    protected $fillable = [
        'booking_number', 'provider_id', 'service_id',
        'service_type', 'client_name', 'customer_email', 'customer_phone',
        'receipt_code_hash', 'receipt_code_issued_at', 'receipt_code_used_at',
        'title', 'pax', 'start_date', 'duration', 'notes', 'amount',
        'price_per_day_snapshot', 'status',
        'confirmed_at', 'completed_at', 'cancelled_at',
        'payment_status', 'payment_method', 'payment_ref', 'paid_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'amount' => 'decimal:2',
        'price_per_day_snapshot' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function service() { return $this->belongsTo(Service::class); }

    /**
     * Auto-generate booking_number on create (concurrency-safe + null-safe).
     * Fix: created_at is NULL during creating event — use booking_number pattern instead.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->booking_number)) {
                $year = date('Y');
                $prefix = 'IND-' . $year . '-';
                $maxSeq = (int) (static::where('booking_number', 'like', $prefix . '%')
                    ->selectRaw('MAX(CAST(SUBSTRING(booking_number, -4) AS UNSIGNED)) as max_seq')
                    ->value('max_seq') ?? 0);
                $model->booking_number = $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}