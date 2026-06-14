<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SosAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'trekker_id',
        'booking_id',
        'latitude',
        'longitude',
        'message',
        'is_resolved',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_resolved' => 'boolean',
    ];

    // Belongs to a trekker
    public function trekker()
    {
        return $this->belongsTo(Trekker::class);
    }

    // Belongs to a booking
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}