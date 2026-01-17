<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'product_id',
        'button_text',
        'button_url',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Slider → Product relation
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Dynamic title fallback
     */
    public function getDisplayTitleAttribute()
    {
        return $this->title ?? $this->product?->title;
    }

    /**
     * Dynamic subtitle fallback
     */
    public function getDisplaySubtitleAttribute()
    {
        return $this->subtitle ?? $this->product?->short_description;
    }

    /**
     * Dynamic button URL
     */
    public function getDisplayUrlAttribute()
    {
        if ($this->button_url) {
            return $this->button_url;
        }

        return $this->product
            ? route('products.show', $this->product->slug)
            : null;
    }
}
