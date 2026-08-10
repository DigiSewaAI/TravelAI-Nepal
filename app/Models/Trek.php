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
        'category',
        'price',
        'itinerary',
        'cover_image',
        'gallery',
        'service_id', // ✅ Phase 3
    ];

    protected $casts = [
        'itinerary' => 'array',
        'gallery' => 'array',
        'price' => 'decimal:2',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // ✅ Phase 3: New relationship
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}