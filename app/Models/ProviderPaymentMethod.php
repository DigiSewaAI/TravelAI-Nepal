<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'type',
        'label',
        'account_name',
        'account_number',
        'identifier',
        'bank_name',
        'swift_code',
        'qr_image_path',
        'currency',
        'instructions',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}