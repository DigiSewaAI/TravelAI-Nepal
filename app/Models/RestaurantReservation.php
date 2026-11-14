<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantReservation extends Model
{
    protected $fillable = [
        'provider_id',
        'table_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'reservation_date',
        'reservation_time',
        'guests',
        'special_requests',
        'status',
    ];

    protected $casts = [
        'reservation_date' => 'date',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }
}