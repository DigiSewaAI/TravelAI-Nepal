<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopDetail extends Model
{
    protected $fillable = [
        'product_id',
        'stock_count',
        'sku',
    ];

    protected $casts = [
        'stock_count' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}