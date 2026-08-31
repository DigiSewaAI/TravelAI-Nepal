<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'waypoint_id', 'booking_id', 'qr_scan_id',
        'media_type', 'file_name', 'original_path', 'optimized_path', 'thumbnail_path',
        'metadata', 'captured_at', 'is_primary', 'source'
    ];

    protected $casts = [
        'metadata' => 'array',
        'captured_at' => 'datetime',
        'is_primary' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function waypoint()
    {
        return $this->belongsTo(Waypoint::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function qrScan()
    {
        return $this->belongsTo(QrScan::class);
    }
}