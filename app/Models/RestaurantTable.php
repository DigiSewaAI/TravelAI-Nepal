<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    protected $fillable = [
        'provider_id', 'service_id', 'table_number', 'seats', 'status',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function service()  { return $this->belongsTo(Service::class); }
    public function orders()   { return $this->hasMany(RestaurantOrder::class, 'table_id'); }

    public function currentOrder()
    {
        return $this->hasOne(RestaurantOrder::class, 'table_id')
            ->whereIn('status', ['pending', 'preparing', 'ready', 'served'])
            ->latestOfMany();
    }
}