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
     * @deprecated (4M-3-REDO) Use providerTypesMany() pivot instead.
     */
    public function providerTypes()
    {
        return $this->hasMany(ProviderType::class);
    }

    /**
     * 4M-3-REDO: Many-to-many provider types allowed for this category.
     */
    public function providerTypesMany()
    {
        return $this->belongsToMany(
            ProviderType::class,
            'provider_type_service_category',
            'service_category_id',
            'provider_type_id'
        );
    }
}