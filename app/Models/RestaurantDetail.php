<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantDetail extends Model
{
    protected $fillable = [
        'service_id', 'cuisine_type', 'seating_capacity', 'opening_hours', 'menu_url'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}