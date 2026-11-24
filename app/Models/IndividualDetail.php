<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndividualDetail extends Model
{
    protected $fillable = [
        'provider_id', 'bio', 'experience_years',
        'languages', 'specialties', 'service_areas',
        'avatar_path', 'cover_image_path',
        'price_per_day', 'currency', 'is_published',
    ];

    protected $casts = [
        'languages' => 'array',
        'specialties' => 'array',
        'service_areas' => 'array',
        'price_per_day' => 'decimal:2',
        'is_published' => 'boolean',
        'experience_years' => 'integer',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}