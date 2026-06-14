<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'trekker_id',
        'trek_id',
        'booking_date',
        'start_date',
        'status',
        'qr_code',
        'invoice_url',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_date' => 'date',
    ];

    // Belongs to a trekker
    public function trekker()
    {
        return $this->belongsTo(Trekker::class);
    }

    // Belongs to a trek
    public function trek()
    {
        return $this->belongsTo(Trek::class);
    }

    // Has many QR scans (checkpoints)
    public function qrScans()
    {
        return $this->hasMany(QrScan::class);
    }

    // Has one SOS alert? Actually SOS belongs to booking and trekker
    public function sosAlert()
    {
        return $this->hasOne(SosAlert::class);
    }
}