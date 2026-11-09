<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SosAlertAction extends Model
{
    protected $table = 'sos_alert_actions';
    public $timestamps = false;

    protected $fillable = [
        'sos_alert_id', 'user_id', 'action',
        'from_status', 'to_status', 'notes', 'metadata', 'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function sosAlert()
    {
        return $this->belongsTo(SosAlert::class, 'sos_alert_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}