<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Affiliate extends Model
{
    protected $fillable = [
        'user_id', 'referral_code', 'status', 'tier',
        'payment_method', 'payment_details', 'bio',
        'website', 'social_media', 'rejection_reason',
        'approved_at', 'suspended_at',
    ];

    protected $casts = [
        'payment_details' => 'array',
        'approved_at'     => 'datetime',
        'suspended_at'    => 'datetime',
    ];

    public const STATUSES = ['pending', 'active', 'suspended', 'rejected'];

    protected static function booted(): void
    {
        static::creating(function (self $affiliate) {
            if (empty($affiliate->referral_code)) {
                do {
                    $code = strtoupper(Str::random(8));
                } while (static::where('referral_code', $code)->exists());
                $affiliate->referral_code = $code;
            }
        });

        static::created(function (self $affiliate) {
            $affiliate->balance()->create([
                'available_balance' => 0,
                'pending_balance'   => 0,
                'total_earned'      => 0,
                'total_paid'        => 0,
                'total_reversed'    => 0,
            ]);
        });
    }

    // ── Relationships ─────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function balance()
    {
        return $this->hasOne(AffiliateBalance::class);
    }

    public function referrals()
    {
        return $this->hasMany(AffiliateReferral::class);
    }

    public function commissions()
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(AffiliateWithdrawal::class);
    }

    public function tierModel()
    {
        return $this->belongsTo(AffiliateTier::class, 'tier', 'slug');
    }

    // ── Scopes ────────────────────────────────────────────

    public function scopeActive($q)    { return $q->where('status', 'active'); }
    public function scopePending($q)   { return $q->where('status', 'pending'); }
    public function scopeSuspended($q) { return $q->where('status', 'suspended'); }

    // ── Helpers ───────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function referralUrl(): string
    {
        $base = rtrim(config('app.frontend_url', config('app.url')), '/');
        return $base . '?ref=' . $this->referral_code;
    }

    public function getCommissionRate(): float
    {
        $tier = AffiliateTier::where('slug', $this->tier)->first();
        return $tier ? (float) $tier->commission_rate : (float) Setting::get('affiliate', 'default_commission_rate', 5.00);
    }

    public function getL2CommissionRate(): float
    {
        $tier = AffiliateTier::where('slug', $this->tier)->first();
        return $tier ? (float) $tier->l2_commission_rate : (float) Setting::get('affiliate', 'default_l2_rate', 2.00);
    }
}
