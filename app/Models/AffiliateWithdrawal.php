<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AffiliateWithdrawal extends Model
{
    protected $fillable = [
        'affiliate_id', 'trx', 'amount', 'fee', 'net_amount',
        'payment_method', 'payment_details', 'status',
        'admin_note', 'rejection_reason',
        'approved_at', 'completed_at',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'fee'             => 'decimal:2',
        'net_amount'      => 'decimal:2',
        'payment_details' => 'array',
        'approved_at'     => 'datetime',
        'completed_at'    => 'datetime',
    ];

    public const STATUSES = ['pending', 'approved', 'rejected', 'completed'];

    protected static function booted(): void
    {
        static::creating(function (self $withdrawal) {
            if (empty($withdrawal->trx)) {
                $withdrawal->trx = 'AW-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    // ── Scopes ────────────────────────────────────────────

    public function scopePending($q)   { return $q->where('status', 'pending'); }
    public function scopeApproved($q)  { return $q->where('status', 'approved'); }
    public function scopeCompleted($q) { return $q->where('status', 'completed'); }

    public static function methodLabels(): array
    {
        return [
            'wallet'   => 'Wallet Transfer',
            'paypal'   => 'PayPal',
            'bank'     => 'Bank Transfer',
            'crypto'   => 'Cryptocurrency',
            'bkash'    => 'bKash',
            'nagad'    => 'Nagad',
            'wise'     => 'Wise',
            'payoneer' => 'Payoneer',
            'skrill'   => 'Skrill',
        ];
    }

    public function getMethodLabelAttribute(): string
    {
        return self::methodLabels()[$this->payment_method] ?? ucfirst($this->payment_method);
    }
}
