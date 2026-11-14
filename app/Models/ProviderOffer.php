<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderOffer extends Model
{
    protected $fillable = [
        'provider_id',
        'title',
        'discount',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}