<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = [
        'slug', 'name', 'description', 'category',
        'icon', 'rarity', 'points', 'criteria', 'is_active'
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Users who have earned this achievement.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
                    ->withPivot('earned_at', 'metadata')
                    ->withTimestamps();
    }
}