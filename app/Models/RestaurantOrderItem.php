<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantOrderItem extends Model
{
    protected $fillable = [
        'restaurant_order_id', 'menu_item_id', 'item_name',
        'unit_price', 'quantity', 'line_total', 'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order()    { return $this->belongsTo(RestaurantOrder::class, 'restaurant_order_id'); }
    public function menuItem() { return $this->belongsTo(MenuItem::class); }
}