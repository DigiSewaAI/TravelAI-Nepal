<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RescueMissionUpdate extends Model
{
    public $timestamps = false; // Only created_at is used

    protected $fillable = ['rescue_mission_id', 'user_id', 'action', 'notes', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function mission() { return $this->belongsTo(RescueMission::class); }
    public function user() { return $this->belongsTo(User::class); }
}