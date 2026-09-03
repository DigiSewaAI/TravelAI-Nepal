<?php

namespace App\Models;

use App\Models\Traits\HasSafetyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory, HasSafetyStatus;

    protected $fillable = [
        'country',
        'state',
        'city',
        'latitude',
        'longitude',
        'is_habitable', // added
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_habitable' => 'boolean',
    ];
}