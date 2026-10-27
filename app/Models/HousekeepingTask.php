<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HousekeepingTask extends Model
{
    protected $fillable = ['provider_id', 'room_id', 'task_name', 'assigned_to', 'status'];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function room()     { return $this->belongsTo(HotelRoom::class, 'room_id'); }
}