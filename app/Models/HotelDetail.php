<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'room_count',
        'star_rating',
        'amenities',
        'check_in_time',
        'check_out_time',
    ];

    protected $casts = [
        'amenities' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}