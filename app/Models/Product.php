<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'product_type',
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
        'price'   => 'decimal:2',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function shopDetail()
    {
        return $this->hasOne(ShopDetail::class);
    }

    public function rentalDetail()
    {
        return $this->hasOne(RentalDetail::class);
    }

    public function wholesaleDetail()
    {
        return $this->hasOne(WholesaleDetail::class);
    }

    /**
     * Polymorphic-like accessor for type-specific details.
     * Returns the related detail model based on product_type.
     */
    public function details()
    {
        return match ($this->product_type) {
            'shop'      => $this->shopDetail,
            'rental'    => $this->rentalDetail,
            'wholesale' => $this->wholesaleDetail,
            default     => null,
        };
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeShop($q)
    {
        return $q->where('product_type', 'shop');
    }

    public function scopeRental($q)
    {
        return $q->where('product_type', 'rental');
    }

    public function scopeWholesale($q)
    {
        return $q->where('product_type', 'wholesale');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    // =============================================
    // HELPERS
    // =============================================

    public function isShop(): bool
    {
        return $this->product_type === 'shop';
    }

    public function isRental(): bool
    {
        return $this->product_type === 'rental';
    }

    public function isWholesale(): bool
    {
        return $this->product_type === 'wholesale';
    }
}