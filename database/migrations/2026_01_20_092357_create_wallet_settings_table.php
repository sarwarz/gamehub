<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_settings', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Wallet Core
            |--------------------------------------------------------------------------
            */
            $table->boolean('wallet_enabled')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Deposit (Topup) Settings
            |--------------------------------------------------------------------------
            */
            $table->boolean('deposit_enabled')->default(true);

            $table->decimal('min_topup_amount', 15, 2)->default(0);
            $table->decimal('max_topup_amount', 15, 2)->nullable();

            // e.g. ["card","local","crypto","bank"]
            $table->json('allowed_payment_gateways')->nullable();

            // Gateway charge
            $table->enum('gateway_charge_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('gateway_charge_amount', 15, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Wallet Usage
            |--------------------------------------------------------------------------
            */
            $table->boolean('partial_payment_enabled')->default(false);
            $table->boolean('auto_deduct_wallet_for_partial')->default(false);

            /*
            |--------------------------------------------------------------------------
            | Wallet Transfer
            |--------------------------------------------------------------------------
            */
            $table->boolean('wallet_transfer_enabled')->default(false);

            $table->decimal('min_transfer_amount', 15, 2)->default(0);

            $table->enum('transfer_charge_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('transfer_charge_amount', 15, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */
            $table->string('currency', 10)->default('USD');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_settings');
    }
};
