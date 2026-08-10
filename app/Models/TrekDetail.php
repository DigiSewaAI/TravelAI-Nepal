<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrekDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'duration_days',
        'difficulty',
        'itinerary',
        'max_altitude',
        'season',
    ];

    protected $casts = [
        'itinerary' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}