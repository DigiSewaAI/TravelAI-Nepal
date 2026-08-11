<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'service_category_id',
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'cover_image',
        'gallery',
        'status',
        'location_id',
    ];

    protected $casts = [
        'gallery' => 'array',
        'price' => 'decimal:2',
    ];

    // Relationships
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // Polymorphic relation for details based on category
    public function details()
    {
        return $this->morphTo();
    }

    // Trek details (if service is a trek)
    public function trekDetail()
    {
        return $this->hasOne(TrekDetail::class);
    }

    public function tourDetail()
    {
        return $this->hasOne(TourDetail::class);
    }

    public function hotelDetail()
    {
        return $this->hasOne(HotelDetail::class);
    }

    // Bookings
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // ========== REVIEW RELATIONS (Phase 10) ==========
    public function reviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function allReviews()
    {
        return $this->hasMany(Review::class);
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function ratingsCount()
    {
        return $this->reviews()->count();
    }
}