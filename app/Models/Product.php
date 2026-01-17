<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'sku',
        'short_description',
        'description',
        'developer_id',
        'publisher_id',
        'cover_image',
        'gallery',
        'attributes',
        'system_requirements',
        'delivery_type',
        'status',
        'is_featured',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'gallery'             => 'array',
        'attributes'          => 'array',
        'system_requirements' => 'array',
        'is_featured'         => 'boolean',
    ];

    /**
     * Automatically set slug
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $value ? Str::slug($value) : Str::slug($this->attributes['title'] ?? '');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Many-to-Many: Product ↔ Categories
    public function categories()
    {
        return $this->belongsToMany(
            ProductCategory::class,
            'category_product',
            'product_id',
            'category_id'
        )->withTimestamps();
    }

    // Many-to-Many: Product ↔ Platforms
    public function platforms()
    {
        return $this->belongsToMany(
            ProductPlatform::class,
            'platform_product',
            'product_id',
            'platform_id'
        )->withTimestamps();
    }

    // Many-to-Many: Product ↔ Types
    public function types()
    {
        return $this->belongsToMany(
            ProductType::class,
            'product_type_product',
            'product_id',
            'type_id'
        )->withTimestamps();
    }

    // Many-to-Many: Product ↔ Regions
    public function regions()
    {
        return $this->belongsToMany(
            ProductRegion::class,
            'product_region_product',
            'product_id',
            'region_id'
        )->withTimestamps();
    }

    // Many-to-Many: Product ↔ Languages
    public function languages()
    {
        return $this->belongsToMany(
            ProductLanguage::class,
            'language_product',
            'product_id',
            'language_id'
        )->withTimestamps();
    }

    // Many-to-Many: Product ↔ WorksOn
    public function worksOn()
    {
        return $this->belongsToMany(
            ProductWorksOn::class,
            'product_works_on_product',
            'product_id',
            'works_on_id'
        )->withTimestamps();
    }

    // One-to-Many: Product ↔ Developer
    public function developer()
    {
        return $this->belongsTo(ProductDeveloper::class);
    }

    // One-to-Many: Product ↔ Publisher
    public function publisher()
    {
        return $this->belongsTo(ProductPublisher::class);
    }

    // One-to-Many: Product ↔ Seller Offers
    public function offers()
    {
        return $this->hasMany(SellerOffer::class, 'product_id');
    }

    public function sliders()
    {
        return $this->hasMany(Slider::class);
    }



    // Scope: Active Products
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
