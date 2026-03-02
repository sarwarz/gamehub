<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'code',
        'description',
        'type',
        'value',
        'max_discount_amount',

        'min_order_amount',
        'max_order_amount',

        'include_categories',
        'exclude_categories',
        'include_products',
        'exclude_products',

        'usage_limit',
        'usage_limit_per_user',

        'starts_at',
        'expires_at',

        'is_active',
    ];

    protected $casts = [
        'include_categories'   => 'array',
        'exclude_categories'   => 'array',
        'include_products'     => 'array',
        'exclude_products'     => 'array',
        'max_discount_amount'  => 'decimal:2',
        'starts_at'            => 'date',
        'expires_at'           => 'date',
        'is_active'            => 'boolean',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function isGlobal(): bool
    {
        return $this->seller_id === null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
