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
        'escrow_expires_at',
        'escrow_released_at',
    ];

    protected $casts = [
        'gross_amount'       => 'decimal:2',
        'commission'         => 'decimal:2',
        'net_amount'         => 'decimal:2',
        'escrow_expires_at'  => 'datetime',
        'escrow_released_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
