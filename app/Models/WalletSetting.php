<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
        'max_daily_deposit_limit',

        // Wallet usage
        'partial_payment_enabled',
        'auto_deduct_wallet_for_partial',
        'max_wallet_balance',

        // Transfer
        'wallet_transfer_enabled',
        'min_transfer_amount',
        'max_transfer_amount',
        'transfer_charge_type',
        'transfer_charge_amount',
        'max_daily_transfer_limit',

        // Withdrawal
        'withdraw_enabled',
        'min_withdraw_amount',
        'max_withdraw_amount',
        'max_daily_withdraw_limit',
        'withdraw_charge_type',
        'withdraw_charge_amount',
        'auto_approve_withdraw',

        // Notifications
        'low_balance_alert_enabled',
        'low_balance_threshold',

        // Currency
        'currency',
    ];

    protected $casts = [
        'wallet_enabled'                 => 'boolean',
        'deposit_enabled'                => 'boolean',
        'partial_payment_enabled'        => 'boolean',
        'auto_deduct_wallet_for_partial' => 'boolean',
        'wallet_transfer_enabled'        => 'boolean',
        'withdraw_enabled'               => 'boolean',
        'auto_approve_withdraw'          => 'boolean',
        'low_balance_alert_enabled'      => 'boolean',

        'allowed_payment_gateways' => 'array',

        'min_topup_amount'          => 'decimal:2',
        'max_topup_amount'          => 'decimal:2',
        'gateway_charge_amount'     => 'decimal:2',
        'max_daily_deposit_limit'   => 'decimal:2',
        'max_wallet_balance'        => 'decimal:2',
        'min_transfer_amount'       => 'decimal:2',
        'max_transfer_amount'       => 'decimal:2',
        'transfer_charge_amount'    => 'decimal:2',
        'max_daily_transfer_limit'  => 'decimal:2',
        'min_withdraw_amount'       => 'decimal:2',
        'max_withdraw_amount'       => 'decimal:2',
        'max_daily_withdraw_limit'  => 'decimal:2',
        'withdraw_charge_amount'    => 'decimal:2',
        'low_balance_threshold'     => 'decimal:2',
    ];

    /**
     * Cached singleton – call this everywhere instead of querying each time.
     */
    public static function global(): self
    {
        return Cache::remember('wallet_settings', 3600, function () {
            return static::first() ?? static::create([
                'wallet_enabled'            => true,
                'deposit_enabled'           => true,
                'partial_payment_enabled'   => false,
                'auto_deduct_wallet_for_partial' => false,
                'wallet_transfer_enabled'   => false,
                'withdraw_enabled'          => false,
                'auto_approve_withdraw'     => false,
                'low_balance_alert_enabled' => false,
                'min_topup_amount'          => 0,
                'gateway_charge_amount'     => 0,
                'transfer_charge_amount'    => 0,
                'withdraw_charge_amount'    => 0,
                'low_balance_threshold'     => 0,
            ]);
        });
    }

    /**
     * Bust the cache whenever settings are saved.
     */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('wallet_settings'));
    }
}
