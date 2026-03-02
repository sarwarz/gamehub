<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'user_id',
        'order_number',
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
        'subtotal'             => 'decimal:2',
        'tax_amount'           => 'decimal:2',
        'discount_amount'      => 'decimal:2',
        'total_amount'         => 'decimal:2',
        'base_subtotal'        => 'decimal:2',
        'base_tax_amount'      => 'decimal:2',
        'base_discount_amount' => 'decimal:2',
        'base_total_amount'    => 'decimal:2',
        'exchange_rate'        => 'decimal:8',
        'meta'                 => 'array',
        'paid_at'              => 'datetime',
        'completed_at'         => 'datetime',
        'cancelled_at'         => 'datetime',
        'refunded_at'          => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */
    protected static function booted()
    {
        static::creating(function ($order) {
            if (! $order->order_number) {
                $maxRetries = 5;

                for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
                    $lastNumber = DB::table('orders')
                        ->lockForUpdate()
                        ->max(DB::raw('CAST(order_number AS UNSIGNED)'));

                    $nextNumber = $lastNumber ? ((int) $lastNumber + 1) : 1;
                    $padded     = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

                    $prefix = Setting::get('store', 'order_prefix', '');
                    $candidate = $prefix ? $prefix . $padded : $padded;
                    $exists = DB::table('orders')->where('order_number', $candidate)->exists();

                    if (!$exists) {
                        $order->order_number = $candidate;
                        return;
                    }
                }

                $prefix = Setting::get('store', 'order_prefix', '');
                $fallback = str_pad(
                    (int) DB::table('orders')->max(DB::raw('CAST(REPLACE(order_number, \'' . addslashes($prefix) . '\', \'\') AS UNSIGNED)')) + 1 + random_int(1, 100),
                    6, '0', STR_PAD_LEFT
                );
                $order->order_number = $prefix ? $prefix . $fallback : $fallback;
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

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function notes()
    {
        return $this->hasMany(OrderNote::class)->latest();
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

    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function sellerEarnings()
    {
        return $this->hasMany(SellerEarning::class);
    }

    public function deliveries()
    {
        return $this->hasManyThrough(OrderDelivery::class, OrderItem::class);
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
