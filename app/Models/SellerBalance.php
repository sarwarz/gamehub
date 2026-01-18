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
        'available_balance' => 'float',
        'pending_balance'   => 'float',
        'total_earned'      => 'float',
        'total_paid'        => 'float',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
