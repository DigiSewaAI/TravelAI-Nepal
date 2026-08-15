<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'provider_id', 'subscription_id', 'booking_id',
        'invoice_number', 'receipt_number', 'amount', 'currency',
        'tax', 'total', 'status', 'payment_method', 'paid_at', 'due_date', 'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'due_date' => 'datetime',
        'metadata' => 'array',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}