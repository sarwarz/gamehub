<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ProductPlatform extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public function setNameAttribute($value)
     {
          $this->attributes['name'] = $value;
          // Only set slug if it's not already set manually
          if (! isset($this->attributes['slug']) || empty($this->attributes['slug'])) {
          $this->attributes['slug'] = Str::slug($value);
          }
     }


     public function setSlugAttribute($value)
     {
          $this->attributes['slug'] = Str::slug($value);
     }


    /**
     * Accessor for active/inactive label
     */
    public function getStatusLabelAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }
}
