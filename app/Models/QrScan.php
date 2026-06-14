<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'checkpoint_name',
        'scanned_at',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Belongs to a booking
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}