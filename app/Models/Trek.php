<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trek extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'name',
        'duration_days',
        'difficulty',
        'category',        // ✅ नयाँ थपियो
        'price',
        'itinerary',
        'cover_image',
        'gallery',
    ];

    protected $casts = [
        'itinerary' => 'array',
        'gallery' => 'array',
        'price' => 'decimal:2',
    ];

    // Belongs to an agency
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    // Has many bookings
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}