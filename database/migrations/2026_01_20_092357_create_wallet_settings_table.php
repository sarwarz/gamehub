<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_settings', function (Blueprint $table) {
            $table->id();

            // Wallet Core
            $table->boolean('wallet_enabled')->default(true);

            // Deposit (Topup) Settings
            $table->boolean('deposit_enabled')->default(true);
            $table->decimal('min_topup_amount', 15, 2)->default(0);
            $table->decimal('max_topup_amount', 15, 2)->nullable();
            $table->json('allowed_payment_gateways')->nullable();
            $table->enum('gateway_charge_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('gateway_charge_amount', 15, 2)->default(0);
            $table->decimal('max_daily_deposit_limit', 15, 2)->nullable();

            // Wallet Usage
            $table->boolean('partial_payment_enabled')->default(false);
            $table->boolean('auto_deduct_wallet_for_partial')->default(false);
            $table->decimal('max_wallet_balance', 15, 2)->nullable();

            // Wallet Transfer
            $table->boolean('wallet_transfer_enabled')->default(false);
            $table->decimal('min_transfer_amount', 15, 2)->default(0);
            $table->decimal('max_transfer_amount', 15, 2)->nullable();
            $table->enum('transfer_charge_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('transfer_charge_amount', 15, 2)->default(0);
            $table->decimal('max_daily_transfer_limit', 15, 2)->nullable();

            // Withdrawal Settings
            $table->boolean('withdraw_enabled')->default(false);
            $table->decimal('min_withdraw_amount', 15, 2)->default(0);
            $table->decimal('max_withdraw_amount', 15, 2)->nullable();
            $table->decimal('max_daily_withdraw_limit', 15, 2)->nullable();
            $table->enum('withdraw_charge_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('withdraw_charge_amount', 15, 2)->default(0);
            $table->boolean('auto_approve_withdraw')->default(false);

            // Low Balance Alert
            $table->boolean('low_balance_alert_enabled')->default(false);
            $table->decimal('low_balance_threshold', 15, 2)->default(0);

            // Currency
            $table->string('currency', 10)->default('USD');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_settings');
    }
};
