<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SellerOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'product_id',

        // Pricing
        'retail_price',
        'retail_acquisition_cost',

        'wholesale_10_99_price',
        'wholesale_10_99_acquisition_cost',

        'wholesale_100_plus_price',
        'wholesale_100_acquisition_cost',

        'sale_mode',
        'status',
        'is_verified',
        'is_promoted',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_promoted' => 'boolean',
        'retail_price' => 'decimal:2',
        'retail_acquisition_cost' => 'decimal:2',
        'wholesale_10_99_price' => 'decimal:2',
        'wholesale_10_99_acquisition_cost' => 'decimal:2',
        'wholesale_100_plus_price' => 'decimal:2',
        'wholesale_100_acquisition_cost' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function keys()
    {
        return $this->hasMany(SellerOfferKey::class);
    }
}
