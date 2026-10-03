<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'product_id', 'quantity',
        'rental_start_date', 'rental_end_date',
    ];

    protected $casts = [
        'rental_start_date' => 'date',
        'rental_end_date'   => 'date',
        'quantity'          => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeForUser($q, $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function scopeForSession($q, $sid)
    {
        return $q->where('session_id', $sid);
    }

    public function getSubtotalAttribute(): float
    {
        $product = $this->product;
        if (!$product) {
            return 0;
        }

        if ($product->isRental() && $this->rental_start_date && $this->rental_end_date) {
            $days = $this->rental_start_date->diffInDays($this->rental_end_date) + 1;
            $perDay = $product->rentalDetail->rental_price_per_day ?? 0;
            return (float) ($perDay * $days * $this->quantity);
        }

        return (float) ($product->price * $this->quantity);
    }
}