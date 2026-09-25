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
     * @deprecated (4M-3-REDO) Use serviceCategories() pivot instead.
     */
    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    /**
     * 4M-3-REDO: Many-to-many categories this provider-type can create.
     */
    public function serviceCategories()
    {
        return $this->belongsToMany(
            ServiceCategory::class,
            'provider_type_service_category',
            'provider_type_id',
            'service_category_id'
        );
    }
}