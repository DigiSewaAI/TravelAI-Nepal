<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'duration_days',
        'itinerary',
        'inclusions',
        'exclusions',
    ];

    protected $casts = [
        'itinerary' => 'array',
        'inclusions' => 'array',
        'exclusions' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}