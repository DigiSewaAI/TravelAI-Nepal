<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportCancellationRequest extends Model
{
    protected $fillable = [
        'transport_booking_id', 'requested_by', 'reason',
        'estimated_refund', 'status', 'refund_status',
        'provider_response', 'responded_at', 'refunded_at',
    ];

    protected $casts = [
        'estimated_refund' => 'decimal:2',
        'responded_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(TransportBooking::class, 'transport_booking_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}