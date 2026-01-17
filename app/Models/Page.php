<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'featured_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
        'show_in_header',
        'show_in_footer',
        'position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_header' => 'boolean',
        'show_in_footer' => 'boolean',
    ];

    /**
     * Route key binding by slug
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
