<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDelivery extends Model
{
    protected $fillable = [
        'order_item_id','delivery_method','payload','status','delivered_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'delivered_at' => 'datetime',
    ];

    public function orderItem() {
        return $this->belongsTo(OrderItem::class);
    }
}
