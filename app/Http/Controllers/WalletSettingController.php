<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletSetting;
use App\Models\PaymentMethod;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Cache;

class WalletSettingController extends Controller
{
    public function edit(CurrencyService $currencyService)
    {
        $walletSetting = WalletSetting::global();

        // Only enabled payment methods
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
            // Deposit
            'min_topup_amount' => 'required|numeric|min:0',
            'max_topup_amount' => 'nullable|numeric|gte:min_topup_amount',

            // Payment methods
            'allowed_payment_gateways' => 'nullable|array',
            'allowed_payment_gateways.*' => 'string|exists:payment_methods,code',

            // Gateway charge
            'gateway_charge_type' => 'required|in:percentage,fixed',
            'gateway_charge_amount' => 'required|numeric|min:0',

            // Transfer
            'min_transfer_amount' => 'nullable|numeric|min:0',
            'transfer_charge_type' => 'required|in:percentage,fixed',
            'transfer_charge_amount' => 'required|numeric|min:0',
        ]);

        $data['currency'] = $currencyService->code();

        // Checkbox-safe booleans
        $data += [
            'wallet_enabled' => $request->boolean('wallet_enabled'),
            'deposit_enabled' => true,

            'partial_payment_enabled' => $request->boolean('partial_payment_enabled'),
            'auto_deduct_wallet_for_partial' => $request->boolean('auto_deduct_wallet_for_partial'),

            'wallet_transfer_enabled' => $request->boolean('wallet_transfer_enabled'),
        ];

        $walletSetting->fill($data)->save();

        Cache::forget('wallet_settings');

        return back()->with('success', 'Wallet settings updated successfully.');
    }
}
