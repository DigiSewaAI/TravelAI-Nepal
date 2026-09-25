<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

        protected $fillable = ['name', 'slug', 'description'];

    // Future: hasMany(Service::class)

    /**
     * Phase 4M-3-1: Provider types that map to this category.
     */
    public function providerTypes()
    {
        return $this->hasMany(ProviderType::class);
    }
}