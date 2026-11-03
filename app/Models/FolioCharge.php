<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FolioCharge extends Model
{
    protected $fillable = [
        'hotel_booking_id', 'description', 'category', 'amount',
        'quantity', 'total', 'notes', 'added_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total'  => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id');
    }

    public function addedByUser()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}