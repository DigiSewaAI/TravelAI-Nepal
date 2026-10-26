<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoneyChangerDetail extends Model
{
    protected $fillable = [
        'service_id', 'supported_currencies', 'exchange_rate_margin', 'license_number'
    ];

    protected $casts = [
        'supported_currencies' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}