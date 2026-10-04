<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'provider_id', 'product_name', 'product_type',
        'unit_price', 'quantity', 'rental_start_date', 'rental_end_date',
        'rental_days', 'rental_deposit', 'bulk_min_qty', 'line_total',
    ];

    protected $casts = [
        'rental_start_date' => 'date',
        'rental_end_date'   => 'date',
        'unit_price'        => 'decimal:2',
        'line_total'        => 'decimal:2',
        'rental_deposit'    => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}