<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id','seller_id','product_id','seller_offer_id',
        'quantity','unit_price','subtotal',
        'delivery_type','delivery_status','status'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function order() { return $this->belongsTo(Order::class); }
    public function seller() { return $this->belongsTo(Seller::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function offer() { return $this->belongsTo(SellerOffer::class); }

    public function deliveries() { return $this->hasMany(OrderDelivery::class); }
    public function earning() { return $this->hasOne(SellerEarning::class); }
}
