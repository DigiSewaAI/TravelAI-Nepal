<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

        public function providers()
    {
        return $this->belongsToMany(Provider::class, 'provider_provider_type');
    }

    /**
     * Phase 4M-3-1: Category this provider-type can create services in.
     * NULL = custom "other" type → all categories allowed (Q5).
     */
    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }
}