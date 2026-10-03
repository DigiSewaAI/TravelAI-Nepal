<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalDetail extends Model
{
    protected $fillable = [
        'product_id',
        'rental_price_per_day',
        'rental_deposit',
        'rental_condition',
        'rental_min_days',
        'rental_max_days',
    ];

    protected $casts = [
        'rental_price_per_day' => 'decimal:2',
        'rental_deposit'       => 'decimal:2',
        'rental_min_days'      => 'integer',
        'rental_max_days'      => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}