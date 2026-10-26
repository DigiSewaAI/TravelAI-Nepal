<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantOrder extends Model
{
    protected $fillable = [
        'order_number', 'provider_id', 'service_id', 'table_id',
        'customer_name', 'customer_phone', 'status',
        'subtotal', 'tax', 'total', 'currency',
        'payment_status', 'paid_at', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax'      => 'decimal:2',
        'total'    => 'decimal:2',
        'paid_at'  => 'datetime',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function service()  { return $this->belongsTo(Service::class); }
    public function table()    { return $this->belongsTo(RestaurantTable::class, 'table_id'); }
    public function items()    { return $this->hasMany(RestaurantOrderItem::class); }
}