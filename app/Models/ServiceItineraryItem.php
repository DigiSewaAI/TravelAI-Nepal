<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceItineraryItem extends Model
{
    protected $fillable = [
        'day_id',
        'title',
        'description',
        'time_of_day',
        'sort_order',
        'is_optional',
        'metadata',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_optional' => 'boolean',
        'metadata' => 'array',
    ];

    public function day()
    {
        return $this->belongsTo(ServiceItineraryDay::class, 'day_id');
    }
}