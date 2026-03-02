<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletSetting;
use App\Models\PaymentMethod;
use App\Services\CurrencyService;

class WalletSettingController extends Controller
{
    public function edit(CurrencyService $currencyService)
    {
        $walletSetting = WalletSetting::global();

        $paymentMethods = PaymentMethod::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code', 'type']);

        return view('content.wallets.wallet-settings', compact(
            'walletSetting',
            'currencyService',
            'paymentMethods'
        ));
    }

    public function update(Request $request, CurrencyService $currencyService)
    {
        $walletSetting = WalletSetting::global();

        $data = $request->validate([
            // ── Deposit ──
            'min_topup_amount'          => 'required|numeric|min:0',
            'max_topup_amount'          => 'nullable|numeric|gte:min_topup_amount',
            'max_daily_deposit_limit'   => 'nullable|numeric|min:0',

            'allowed_payment_gateways'   => 'nullable|array',
            'allowed_payment_gateways.*' => 'string|exists:payment_methods,code',

            'gateway_charge_type'   => 'required|in:percentage,fixed',
            'gateway_charge_amount' => 'required|numeric|min:0',

            // ── Usage & Limits ──
            'max_wallet_balance' => 'nullable|numeric|min:0',

            // ── Transfer ──
            'min_transfer_amount'      => 'nullable|numeric|min:0',
            'max_transfer_amount'      => 'nullable|numeric|min:0',
            'max_daily_transfer_limit' => 'nullable|numeric|min:0',
            'transfer_charge_type'     => 'required|in:percentage,fixed',
            'transfer_charge_amount'   => 'required|numeric|min:0',

            // ── Withdrawal ──
            'min_withdraw_amount'      => 'nullable|numeric|min:0',
            'max_withdraw_amount'      => 'nullable|numeric|min:0',
            'max_daily_withdraw_limit' => 'nullable|numeric|min:0',
            'withdraw_charge_type'     => 'required|in:percentage,fixed',
            'withdraw_charge_amount'   => 'required|numeric|min:0',

            // ── Notifications ──
            'low_balance_threshold' => 'nullable|numeric|min:0',
        ]);

        $data['currency'] = $currencyService->code();

        $data += [
            'wallet_enabled'                 => $request->boolean('wallet_enabled'),
            'deposit_enabled'                => $request->boolean('deposit_enabled'),
            'partial_payment_enabled'        => $request->boolean('partial_payment_enabled'),
            'auto_deduct_wallet_for_partial' => $request->boolean('auto_deduct_wallet_for_partial'),
            'wallet_transfer_enabled'        => $request->boolean('wallet_transfer_enabled'),
            'withdraw_enabled'               => $request->boolean('withdraw_enabled'),
            'auto_approve_withdraw'          => $request->boolean('auto_approve_withdraw'),
            'low_balance_alert_enabled'      => $request->boolean('low_balance_alert_enabled'),
        ];

        $walletSetting->fill($data)->save();

        return back()->with('success', 'Wallet settings updated successfully.');
    }
}
