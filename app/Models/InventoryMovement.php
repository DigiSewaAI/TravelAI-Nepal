<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'provider_id', 'product_id', 'type', 'quantity',
        'previous_stock', 'new_stock', 'reason', 'reference',
        'notes', 'created_by',
    ];

    protected $casts = [
        'quantity'       => 'integer',
        'previous_stock' => 'integer',
        'new_stock'      => 'integer',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function product()  { return $this->belongsTo(Product::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
}