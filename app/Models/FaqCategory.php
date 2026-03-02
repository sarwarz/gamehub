<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FaqCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'position', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if (empty($this->attributes['slug'] ?? '')) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    public function faqs()
    {
        return $this->hasMany(Faq::class)->orderBy('position');
    }

    public function activeFaqs()
    {
        return $this->faqs()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
