<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutSession extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'idempotency_key',
        'items',
        'billing',
        'currency',
        'base_currency',
        'base_subtotal',
        'base_tax_amount',
        'base_discount_amount',
        'base_total_amount',
        'exchange_rate',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'wallet_amount',
        'gateway_amount',
        'coupon_id',
        'coupon_data',
        'tax_data',
        'payment_method',
        'gateway_reference',
        'trx',
        'reserved_key_ids',
        'status',
        'meta',
        'expires_at',
        'paid_at',
    ];

    protected $casts = [
        'items'                => 'array',
        'billing'              => 'array',
        'coupon_data'          => 'array',
        'tax_data'             => 'array',
        'reserved_key_ids'     => 'array',
        'meta'                 => 'array',
        'subtotal'             => 'decimal:2',
        'tax_amount'           => 'decimal:2',
        'discount_amount'      => 'decimal:2',
        'total_amount'         => 'decimal:2',
        'base_subtotal'        => 'decimal:2',
        'base_tax_amount'      => 'decimal:2',
        'base_discount_amount' => 'decimal:2',
        'base_total_amount'    => 'decimal:2',
        'exchange_rate'        => 'decimal:8',
        'wallet_amount'        => 'decimal:2',
        'gateway_amount'       => 'decimal:2',
        'expires_at'           => 'datetime',
        'paid_at'              => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->expires_at->isPast();
    }

    public function isOpen(): bool
    {
        return $this->status === 'open' && !$this->expires_at->isPast();
    }

    public function isPaying(): bool
    {
        return $this->status === 'paying' && !$this->expires_at->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
