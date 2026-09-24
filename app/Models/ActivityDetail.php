<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'max_pax',
    ];

    protected $casts = [
        'max_pax' => 'integer',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}