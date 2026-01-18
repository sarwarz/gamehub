<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'user_id',
        'order_number',
        'currency',
        'subtotal',
        'total_amount',

        // Payment
        'payment_method',
        'payment_gateway',
        'payment_reference',
        'payment_status',

        // Order status
        'status',

        // Lifecycle
        'paid_at',
        'completed_at',
        'cancelled_at',
        'refunded_at',

        // Meta
        'meta',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'subtotal'     => 'decimal:2',
        'total_amount' => 'decimal:2',
        'meta'         => 'array',
        'paid_at'      => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */
    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(10));
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Buyer
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Order items
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Order addresses
     */
    public function addresses()
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function billingAddress()
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'billing');
    }

    public function shippingAddress()
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'shipping');
    }

    /**
     * Transactions (ledger)
     */
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'reference');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
