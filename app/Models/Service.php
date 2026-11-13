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
        'itinerary_status',
        'location_id',
        // 🆕 Rating aggregate fields (for recalculateRating() update())
        'average_rating',
        'reviews_count',
        'rating_breakdown',
    ];

    protected $casts = [
        'gallery' => 'array',
        'price' => 'decimal:2',
        'average_rating'   => 'decimal:2',
        'reviews_count'    => 'integer',
        'rating_breakdown' => 'array',
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
        public function restaurantDetail()
    {
        return $this->hasOne(RestaurantDetail::class);
    }

    public function moneyChangerDetail()
    {
        return $this->hasOne(MoneyChangerDetail::class);
    }

    public function artCraftDetail()
    {
        return $this->hasOne(ArtCraftDetail::class);
    }
    public function activityDetail()
    {
        return $this->hasOne(ActivityDetail::class);
    }

        public function experienceDetail()
    {
        return $this->hasOne(ExperienceDetail::class);
    }

    public function transportDetail()
    {
        return $this->hasOne(TransportDetail::class);
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

    /**
     * 🆕 Approved reviews only (public display)
     */
    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    /**
     * 🆕 Recalculate aggregates from APPROVED reviews
     */
    public function recalculateRating(): void
    {
        $approved = $this->reviews()->where('status', 'approved');

        $count = $approved->count();
        $avg   = $count > 0 ? round($approved->avg('rating'), 2) : 0.00;

        $breakdown = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($approved->get(['rating']) as $r) {
            $breakdown[(int) $r->rating] = ($breakdown[(int) $r->rating] ?? 0) + 1;
        }

        $this->update([
            'reviews_count'    => $count,
            'average_rating'   => $avg,
            'rating_breakdown' => $breakdown,
        ]);
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
    // =====================================================
    // PROVIDER ITINERARY (PROVIDER-ITINERARY-04)
    // =====================================================

        public function itineraryDays()
    {
        return $this->hasMany(ServiceItineraryDay::class)
            ->orderBy('day_number');
    }

    // =====================================================
    // PROVIDER-ITINERARY-09B-01: Departures
    // =====================================================

    public function departures()
    {
        return $this->hasMany(Departure::class);
    }

    // =====================================================
    // PROVIDER-ITINERARY-07: Lifecycle helpers
    // =====================================================

    public function isItineraryPublished(): bool
    {
        return $this->itinerary_status === 'published';
    }

    public function isItineraryDraft(): bool
    {
        return $this->itinerary_status === 'draft';
    }

}