<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateTier extends Model
{
    protected $fillable = [
        'name', 'slug', 'commission_rate', 'l2_commission_rate',
        'min_earnings_threshold', 'min_referrals', 'min_conversions',
        'color', 'sort_order', 'is_default',
    ];

    protected $casts = [
        'commission_rate'         => 'decimal:2',
        'l2_commission_rate'      => 'decimal:2',
        'min_earnings_threshold'  => 'decimal:2',
        'min_referrals'           => 'integer',
        'min_conversions'         => 'integer',
        'sort_order'              => 'integer',
        'is_default'              => 'boolean',
    ];

    public function affiliates()
    {
        return $this->hasMany(Affiliate::class, 'tier', 'slug');
    }

    public function scopeDefault($q)
    {
        return $q->where('is_default', true);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('sort_order');
    }
}
