<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RescueDispatchLock extends Model
{
    protected $fillable = ['sos_alert_id', 'provider_id', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];
}