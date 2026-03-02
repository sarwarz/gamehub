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

    /**
     * Resolve the unit price based on quantity and sale_mode.
     * Applies wholesale tiers: 100+ → wholesale_100_plus, 10-99 → wholesale_10_99, else retail.
     */
    public function resolveUnitPrice(int $quantity): float
    {
        $canWholesale = in_array($this->sale_mode, ['wholesale', 'both']);

        if ($canWholesale && $quantity >= 100 && $this->wholesale_100_plus_price) {
            return (float) $this->wholesale_100_plus_price;
        }

        if ($canWholesale && $quantity >= 10 && $this->wholesale_10_99_price) {
            return (float) $this->wholesale_10_99_price;
        }

        return (float) $this->retail_price;
    }
}
