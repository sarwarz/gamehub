<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',

        'min_order_amount',
        'max_order_amount',

        'include_categories',
        'exclude_categories',
        'include_products',
        'exclude_products',

        'usage_limit',
        'usage_limit_per_user',
        'used',

        'starts_at',
        'expires_at',

        'is_active',
    ];

    protected $casts = [
        'include_categories' => 'array',
        'exclude_categories' => 'array',
        'include_products'   => 'array',
        'exclude_products'   => 'array',

        'starts_at' => 'date',
        'expires_at'=> 'date',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Helper Methods (Optional but Recommended)
    |--------------------------------------------------------------------------
    */

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
