<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerWithdraw extends Model
{
    protected $fillable = [
        'seller_id',
        'amount',
        'method',
        'payment_details',
        'status',
        'note',
        'admin_note',
        'transaction_id',
        'processed_at',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'payment_details' => 'array',
        'processed_at'    => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public static function methodLabels(): array
    {
        return [
            'paypal'        => 'PayPal',
            'bank'          => 'Bank Transfer',
            'crypto'        => 'Cryptocurrency',
            'bkash'         => 'bKash',
            'nagad'         => 'Nagad',
            'wise'          => 'Wise (TransferWise)',
            'payoneer'      => 'Payoneer',
            'skrill'        => 'Skrill',
        ];
    }

    public function getMethodLabelAttribute(): string
    {
        return self::methodLabels()[$this->method] ?? ucfirst($this->method);
    }
}
