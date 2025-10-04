<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seller_payment_infos', function (Blueprint $table) {
            $table->id();

            // Relation with sellers
            $table->foreignId('seller_id')->constrained()->onDelete('cascade');

            // Current balances
            $table->decimal('current_balance', 12, 2)->default(0.00);   // Available funds
            $table->decimal('payout_balance', 12, 2)->default(0.00);    // Requested for payout
            $table->decimal('pending_balance', 12, 2)->default(0.00);   // Pending clearance

            // Bank transfer details
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_swift_code')->nullable();

            // PayPal or other payment method
            $table->string('paypal_email')->nullable();
            $table->string('stripe_account_id')->nullable();
            $table->string('crypto_wallet')->nullable();

            // Payout preferences
            $table->enum('preferred_method', ['bank', 'paypal', 'stripe', 'crypto'])->default('bank');
            $table->decimal('minimum_payout', 12, 2)->default(50.00); // e.g. must have $50 to withdraw

            // Status
            $table->boolean('is_verified')->default(false); // Payment info verification

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_payment_infos');
    }
};
