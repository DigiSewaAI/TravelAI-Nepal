<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomOrderQuote extends Model
{
    protected $fillable = [
        'custom_order_id', 'version', 'quoted_amount', 'currency',
        'materials', 'dimensions', 'quantity', 'production_days',
        'delivery_terms', 'advance_proposal', 'valid_until',
        'provider_message', 'status', 'customer_response', 'responded_at',
    ];

    protected $casts = [
        'quoted_amount' => 'decimal:2',
        'advance_proposal' => 'decimal:2',
        'quantity' => 'integer',
        'production_days' => 'integer',
        'valid_until' => 'date',
        'responded_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(CustomOrder::class, 'custom_order_id');
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isImmutable(): bool
    {
        return in_array($this->status, ['accepted', 'rejected', 'superseded'], true);
    }
}