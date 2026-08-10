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
        // We will define this based on category; for now we'll use separate relations
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

    // Bookings (future)
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}