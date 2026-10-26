<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'provider_id', 'service_id', 'name', 'description', 'price', 'currency',
        'category', 'emoji', 'image_path', 'is_available', 'prep_time_minutes',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function provider()    { return $this->belongsTo(Provider::class); }
    public function service()     { return $this->belongsTo(Service::class); }
    public function orderItems()  { return $this->hasMany(RestaurantOrderItem::class); }
}