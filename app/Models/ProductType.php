<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    protected $fillable = ['name', 'slug', 'status', 'commission'];

    /**
     * Automatically set the slug when the name is set
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;

        // Only set slug if it's not already set manually
        if (! isset($this->attributes['slug']) || empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    /**
     * Optionally, ensure slug is always slugified if set directly
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = Str::slug($value);
    }

    /**
     * Ensure commission is always stored as decimal (float)
     */
    public function setCommissionAttribute($value)
    {
        $this->attributes['commission'] = $value !== null ? (float) $value : 0.00;
    }

    /**
     * Accessor to format commission with % symbol
     */
    public function getCommissionFormattedAttribute()
    {
        return number_format($this->commission, 2) . '%';
    }
}
