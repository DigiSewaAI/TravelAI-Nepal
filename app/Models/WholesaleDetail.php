<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleDetail extends Model
{
    protected $fillable = [
        'product_id',
        'min_order_qty',
        'bulk_pricing',
    ];

    protected $casts = [
        'min_order_qty' => 'integer',
        'bulk_pricing'  => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the applicable tier price for a given quantity.
     */
    public function getPriceForQty(int $qty): ?float
    {
        $tiers = $this->bulk_pricing ?? [];
        if (empty($tiers)) {
            return null;
        }

        // Sort descending by min_qty
        usort($tiers, fn($a, $b) => ($b['min_qty'] ?? 0) <=> ($a['min_qty'] ?? 0));

        foreach ($tiers as $tier) {
            if ($qty >= ($tier['min_qty'] ?? 0)) {
                return (float) ($tier['price'] ?? 0);
            }
        }

        return null;
    }
}