<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'location', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if (empty($this->attributes['slug'] ?? '')) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    public function items()
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('position');
    }

    public function allItems()
    {
        return $this->hasMany(MenuItem::class)->orderBy('position');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLocation($query, string $location)
    {
        return $query->where('location', $location);
    }
}
