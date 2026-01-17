<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
        'position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    /**
     * Category → Posts relation (future use)
     */
    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }
}
