<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'user_id',

        // Store details
        'store_name',
        'slug',
        'logo',
        'banner',
        'description',

        // Contact info
        'email',
        'phone',
        'website',

        // Business / Legal
        'company_name',
        'registration_number',
        'vat_number',
        'tax_id',

        // Location
        'country',
        'state',
        'city',
        'address',
        'postal_code',

        // Stats & status
        'rating',
        'total_sales',
        'total_products',
        'status',
        'is_verified',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'rating'          => 'float',
        'total_sales'     => 'integer',
        'total_products'  => 'integer',
        'is_verified'     => 'boolean',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Seller belongs to a user account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function balance()
    {
        return $this->hasOne(SellerBalance::class);
    }

    public function earnings()
    {
        return $this->hasMany(SellerEarning::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(SellerWithdraw::class);
    }




    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Only active sellers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Only verified sellers.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Sellers pending approval.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get store public URL.
     */
    public function getStoreUrlAttribute()
    {
        return url('/store/' . $this->slug);
    }

    /**
     * Display rating with fallback.
     */
    public function getDisplayRatingAttribute()
    {
        return number_format($this->rating ?? 0, 2);
    }
}
