<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateReferral extends Model
{
    protected $fillable = [
        'affiliate_id', 'referred_user_id', 'ip_address',
        'user_agent', 'landing_page', 'referral_source',
        'status', 'registered_at', 'converted_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'converted_at'  => 'datetime',
    ];

    public const STATUSES = ['clicked', 'registered', 'converted'];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function commissions()
    {
        return $this->hasMany(AffiliateCommission::class, 'referral_id');
    }

    // ── Scopes ────────────────────────────────────────────

    public function scopeClicked($q)     { return $q->where('status', 'clicked'); }
    public function scopeRegistered($q)  { return $q->where('status', 'registered'); }
    public function scopeConverted($q)   { return $q->where('status', 'converted'); }
}
