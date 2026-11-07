<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomOrder extends Model
{
    protected $fillable = [
        'provider_id', 'client_name', 'client_email',
        'type', 'budget', 'deadline', 'status'
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'deadline' => 'date',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }

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
                $model->booking_number = 'ART-' . $year . '-' . $nextNum;
            }
        });
    }
}