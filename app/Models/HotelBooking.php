<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelBooking extends Model
{
    protected $fillable = [
        'provider_id', 'room_id', 'guest_name', 'guest_email',
        'check_in_date', 'check_out_date', 'nights', 'amount',
        'payment_status', 'status', 'qr_code'
    ];

    protected $casts = [
        'check_in_date'  => 'date',
        'check_out_date' => 'date',
        'amount'         => 'decimal:2',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function room()     { return $this->belongsTo(HotelRoom::class, 'room_id'); }

    public function folioCharges()
    {
        return $this->hasMany(FolioCharge::class)->orderBy('id');
    }

    public function folioTotal()
    {
        return $this->folioCharges()->sum('total');
    }

    /**
     * Auto-generate booking_number on create.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->booking_number)) {
                $year = date('Y');
                $lastId = static::whereYear('created_at', $year)->max('id') ?? 0;
                $nextNum = str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
                $model->booking_number = 'HTL-' . $year . '-' . $nextNum;
            }
            if (empty($model->qr_code)) {
                $model->qr_code = \Illuminate\Support\Str::random(32);
            }
        });
    }
}