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
        'traveler_id', // ✅ Phase 4
        'service_id',  // ✅ Phase 4
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_date' => 'date',
    ];

    // Old relationships (keep for backward compatibility)
    public function trekker()
    {
        return $this->belongsTo(Trekker::class);
    }

    public function trek()
    {
        return $this->belongsTo(Trek::class);
    }

    // ✅ Phase 4: New relationships
    public function traveler()
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function qrScans()
    {
        return $this->hasMany(QrScan::class);
    }

    public function sosAlert()
    {
        return $this->hasOne(SosAlert::class);
    }
}