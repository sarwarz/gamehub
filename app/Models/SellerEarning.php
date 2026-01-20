<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerEarning extends Model
{
    protected $fillable = [
        'seller_id',
        'order_id',
        'order_item_id',
        'gross_amount',
        'commission',
        'net_amount',
        'status',
    ];

    protected $casts = [
        'gross_amount' => 'float',
        'commission'   => 'float',
        'net_amount'   => 'float',
    ];

    /**
     * Each earning belongs to a seller
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
