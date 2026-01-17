<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seller_id',
        'reference_type',
        'reference_id',
        'trx',
        'amount',
        'fee',
        'net_amount',
        'currency',
        'type',
        'category',
        'status',
        'payment_method',
        'gateway',
        'description',
        'meta',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'fee'        => 'decimal:2',
        'net_amount' => 'decimal:2',
        'meta'       => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    // Polymorphic reference (Order, SellerWithdraw, Refund, etc.)
    public function reference()
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeCredit($q)
    {
        return $q->where('type', 'credit');
    }

    public function scopeDebit($q)
    {
        return $q->where('type', 'debit');
    }

    public function scopeCompleted($q)
    {
        return $q->where('status', 'completed');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }

    public function isDebit(): bool
    {
        return $this->type === 'debit';
    }
}
