<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id','seller_id','product_id','seller_offer_id','quantity','unit_price','subtotal','status'];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function seller() {
        return $this->belongsTo(Seller::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function offer() {
        return $this->belongsTo(SellerOffer::class, 'seller_offer_id');
    }

    public function keys() {
        return $this->hasMany(OrderItemKey::class);
    }
}

