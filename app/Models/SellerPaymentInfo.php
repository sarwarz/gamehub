<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerPaymentInfo extends Model
{
    protected $fillable = [
        'seller_id', 
        'current_balance', 'payout_balance', 'pending_balance',
        'bank_account_name', 'bank_account_number', 'bank_name', 'bank_swift_code',
        'paypal_email', 'stripe_account_id', 'crypto_wallet',
        'preferred_method', 'minimum_payout', 'is_verified'
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
