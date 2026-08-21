<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlannerRequest extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'route_id', 'destination',
        'days', 'budget', 'travel_style', 'interests'
    ];

    protected $casts = [
        'days' => 'integer',
        'budget' => 'decimal:2',
        'interests' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function result()
    {
        return $this->hasOne(PlannerResult::class);
    }
}