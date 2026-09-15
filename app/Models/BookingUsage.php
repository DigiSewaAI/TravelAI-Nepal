<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingUsage extends Model
{
    use HasFactory;

    protected $table = 'booking_usage';

    protected $fillable = ['provider_id', 'month', 'count'];

    protected $casts = [
        'count' => 'integer',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}