<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemKey extends Model
{
    protected $fillable = ['order_item_id','seller_offer_key_id','key_type','key_value','status'];

    public function orderItem() {
        return $this->belongsTo(OrderItem::class);
    }

    public function offerKey() {
        return $this->belongsTo(SellerOfferKey::class, 'seller_offer_key_id');
    }
}
