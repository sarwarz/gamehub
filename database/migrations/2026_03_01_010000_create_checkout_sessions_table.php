<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('idempotency_key')->nullable()->unique();

            $table->json('items');
            $table->json('billing');
            $table->string('currency', 10);
            $table->string('base_currency', 10)->nullable();
            $table->decimal('base_subtotal', 14, 2)->nullable();
            $table->decimal('base_tax_amount', 14, 2)->nullable();
            $table->decimal('base_discount_amount', 14, 2)->nullable();
            $table->decimal('base_total_amount', 14, 2)->nullable();
            $table->decimal('exchange_rate', 15, 8)->default(1.00000000);

            $table->decimal('subtotal', 14, 2);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2);
            $table->decimal('wallet_amount', 14, 2)->default(0);
            $table->decimal('gateway_amount', 14, 2);

            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->json('coupon_data')->nullable();
            $table->json('tax_data')->nullable();

            $table->string('payment_method')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->string('trx', 50)->unique();
            $table->json('reserved_key_ids')->nullable();

            $table->enum('status', ['open', 'paying', 'completed', 'expired'])->default('open');
            $table->json('meta')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_sessions');
    }
};
