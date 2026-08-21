<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteCost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'route_id', 'type', 'name', 'amount', 'currency',
        'unit', 'is_mandatory', 'metadata',
        'effective_from', 'effective_until'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'metadata' => 'array',
        'effective_from' => 'date',
        'effective_until' => 'date:nullable',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}