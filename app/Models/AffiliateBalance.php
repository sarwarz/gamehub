<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateBalance extends Model
{
    protected $fillable = [
        'affiliate_id',
        'available_balance',
        'pending_balance',
        'total_earned',
        'total_paid',
        'total_reversed',
    ];

    protected $casts = [
        'available_balance' => 'decimal:2',
        'pending_balance'   => 'decimal:2',
        'total_earned'      => 'decimal:2',
        'total_paid'        => 'decimal:2',
        'total_reversed'    => 'decimal:2',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}
