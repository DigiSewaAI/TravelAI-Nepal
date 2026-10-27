<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelBooking extends Model
{
    protected $fillable = [
        'provider_id', 'room_id', 'guest_name', 'guest_email',
        'check_in_date', 'check_out_date', 'nights', 'amount',
        'payment_status', 'status'
    ];

    protected $casts = [
        'check_in_date'  => 'date',
        'check_out_date' => 'date',
        'amount'         => 'decimal:2',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function room()     { return $this->belongsTo(HotelRoom::class, 'room_id'); }
}