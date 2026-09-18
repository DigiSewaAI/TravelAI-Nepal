<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceItineraryDayMedia extends Model
{
    protected $fillable = [
        'day_id',
        'file_path',
        'thumbnail_path',
        'media_type',
        'alt_text',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function day()
    {
        return $this->belongsTo(ServiceItineraryDay::class, 'day_id');
    }
}