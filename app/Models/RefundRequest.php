<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{
    protected $fillable = [
        'order_id', 'order_item_id', 'user_id', 'seller_id',
        'type', 'amount', 'reason', 'description', 'evidence',
        'status', 'admin_note', 'processed_by', 'processed_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'evidence'     => 'array',
        'processed_at' => 'datetime',
    ];

    public function order()    { return $this->belongsTo(Order::class); }
    public function orderItem(){ return $this->belongsTo(OrderItem::class); }
    public function user()     { return $this->belongsTo(User::class); }
    public function seller()   { return $this->belongsTo(Seller::class); }
    public function processor(){ return $this->belongsTo(User::class, 'processed_by'); }

    public function scopePending($q)  { return $q->where('status', 'pending'); }
    public function scopeApproved($q) { return $q->where('status', 'approved'); }
}
