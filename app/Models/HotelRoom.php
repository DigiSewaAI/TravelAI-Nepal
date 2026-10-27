<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelRoom extends Model
{
    protected $fillable = ['provider_id', 'room_number', 'room_type', 'floor', 'status'];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function bookings() { return $this->hasMany(HotelBooking::class, 'room_id'); }
}