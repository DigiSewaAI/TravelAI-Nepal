<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trekker extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'emergency_contact',
    ];

    // A trekker can have many bookings
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // A trekker can have many SOS alerts
    public function sosAlerts()
    {
        return $this->hasMany(SosAlert::class);
    }
    public function user()
{
    return $this->belongsTo(User::class, 'email', 'email');
}
}