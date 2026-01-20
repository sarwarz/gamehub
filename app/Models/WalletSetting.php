<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletSetting extends Model
{
    protected $fillable = [
        // Core
        'wallet_enabled',

        // Deposit
        'deposit_enabled',
        'min_topup_amount',
        'max_topup_amount',
        'allowed_payment_gateways',
        'gateway_charge_type',
        'gateway_charge_amount',

        // Wallet usage
        'partial_payment_enabled',
        'auto_deduct_wallet_for_partial',

        // Transfer
        'wallet_transfer_enabled',
        'min_transfer_amount',
        'transfer_charge_type',
        'transfer_charge_amount',

        // Currency
        'currency',
    ];

    protected $casts = [
        // Booleans
        'wallet_enabled' => 'boolean',
        'deposit_enabled' => 'boolean',
        'partial_payment_enabled' => 'boolean',
        'auto_deduct_wallet_for_partial' => 'boolean',
        'wallet_transfer_enabled' => 'boolean',

        // JSON
        'allowed_payment_gateways' => 'array',
    ];

    /**
     * Singleton wallet settings
     */
    public static function global(): self
    {
        return static::first() ?? static::create([
            'wallet_enabled' => true,
            'deposit_enabled' => true,
            'partial_payment_enabled' => false,
            'auto_deduct_wallet_for_partial' => false,
            'wallet_transfer_enabled' => false,
            'min_topup_amount' => 0,
            'gateway_charge_amount' => 0,
            'transfer_charge_amount' => 0,
        ]);
    }
}
