<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ProductWorksOn extends Model
{
    protected $table = 'product_works_on';

    protected $fillable = ['name', 'slug', 'icon', 'status'];

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
}
