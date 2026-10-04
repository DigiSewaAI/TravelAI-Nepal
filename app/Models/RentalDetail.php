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
        'late_fee_per_day',
        'damage_deposit_pct',
        'lost_deposit_pct',
    ];

    protected $casts = [
        'rental_price_per_day' => 'decimal:2',
        'rental_deposit'       => 'decimal:2',
        'rental_min_days'      => 'integer',
        'rental_max_days'      => 'integer',
        'late_fee_per_day'     => 'decimal:2',
        'damage_deposit_pct'   => 'integer',
        'lost_deposit_pct'     => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}