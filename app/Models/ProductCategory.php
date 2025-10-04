<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use SoftDeletes;

    protected $table = 'product_categories';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    /**
     * Scope: only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

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
