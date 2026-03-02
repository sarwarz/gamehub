<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateCommission extends Model
{
    protected $fillable = [
        'affiliate_id', 'order_id', 'order_item_id', 'referral_id',
        'order_amount', 'commission_rate', 'commission_amount',
        'level', 'status', 'held_at', 'available_at',
        'paid_at', 'reversed_at', 'reversal_reason',
    ];

    protected $casts = [
        'order_amount'      => 'decimal:2',
        'commission_rate'   => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'held_at'           => 'datetime',
        'available_at'      => 'datetime',
        'paid_at'           => 'datetime',
        'reversed_at'       => 'datetime',
    ];

    public const STATUSES = ['pending', 'held', 'available', 'paid', 'reversed'];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function referral()
    {
        return $this->belongsTo(AffiliateReferral::class, 'referral_id');
    }

    // ── Scopes ────────────────────────────────────────────

    public function scopePending($q)   { return $q->where('status', 'pending'); }
    public function scopeHeld($q)      { return $q->where('status', 'held'); }
    public function scopeAvailable($q) { return $q->where('status', 'available'); }
    public function scopeReversed($q)  { return $q->where('status', 'reversed'); }
}
