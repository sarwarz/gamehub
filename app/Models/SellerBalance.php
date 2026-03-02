<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerBalance extends Model
{
    protected $fillable = [
        'seller_id',
        'available_balance',
        'pending_balance',
        'total_earned',
        'total_paid',
    ];

    protected $casts = [
        'available_balance' => 'decimal:2',
        'pending_balance'   => 'decimal:2',
        'total_earned'      => 'decimal:2',
        'total_paid'        => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
