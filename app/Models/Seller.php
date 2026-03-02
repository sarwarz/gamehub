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

        // Status (admin-managed)
        'status',
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

    public function offers()
    {
        return $this->hasMany(SellerOffer::class);
    }

    /**
     * Sync the seller + customer roles on the linked user based on seller status.
     */
    public function syncUserRoles(): void
    {
        $user = $this->user;
        if (!$user) return;

        $sellerRole   = \App\Models\Role::where('name', 'seller')->first();
        $customerRole = \App\Models\Role::where('name', 'customer')->first();

        if (!$sellerRole) return;

        if ($this->status === 'active') {
            $roleIds = [$sellerRole->id];
            if ($customerRole) $roleIds[] = $customerRole->id;
            $user->roles()->syncWithoutDetaching($roleIds);
        } else {
            $user->roles()->detach($sellerRole->id);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Rating & Stats
    |--------------------------------------------------------------------------
    */

    /**
     * Recalculate rating from approved reviews on products this seller has offers on.
     */
    public function recalculateRating(): void
    {
        $productIds = $this->offers()->pluck('product_id')->unique();

        if ($productIds->isEmpty()) {
            $this->update(['rating' => 0]);
            return;
        }

        $avg = ProductReview::whereIn('product_id', $productIds)
            ->where('status', 'approved')
            ->avg('rating');

        $this->update(['rating' => round($avg ?? 0, 2)]);
    }

    /**
     * Recalculate total_sales from completed order items.
     */
    public function recalculateSales(): void
    {
        $this->update([
            'total_sales'    => OrderItem::where('seller_id', $this->id)
                                    ->whereHas('order', fn ($q) => $q->where('status', 'completed'))
                                    ->sum('quantity'),
            'total_products' => $this->offers()->where('status', 'active')->distinct('product_id')->count('product_id'),
        ]);
    }

    /**
     * Recalculate all stats at once.
     */
    public function recalculateStats(): void
    {
        $this->recalculateRating();
        $this->recalculateSales();
    }

    /**
     * Recalculate ratings for all sellers linked to a given product.
     */
    public static function recalculateRatingsForProduct(int $productId): void
    {
        $sellerIds = SellerOffer::where('product_id', $productId)->pluck('seller_id')->unique();

        foreach (static::whereIn('id', $sellerIds)->get() as $seller) {
            $seller->recalculateRating();
        }
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
