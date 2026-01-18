<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Buyer
            |--------------------------------------------------------------------------
            */
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Order Identity
            |--------------------------------------------------------------------------
            */
            $table->string('order_number')->unique();
            $table->string('currency', 3)->default('USD');

            /*
            |--------------------------------------------------------------------------
            | Amounts
            |--------------------------------------------------------------------------
            */
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */
            $table->string('payment_method')->nullable(); // stripe, paypal, wallet
            $table->string('payment_gateway')->nullable(); // stripe, sslcommerz
            $table->string('payment_reference')->nullable(); // gateway trx id
            $table->enum('payment_status', [
                'unpaid', 'paid', 'failed', 'refunded'
            ])->default('unpaid');

            /*
            |--------------------------------------------------------------------------
            | Order Status
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'cancelled',
                'refunded'
            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Lifecycle Timestamps
            |--------------------------------------------------------------------------
            */
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Meta
            |--------------------------------------------------------------------------
            */
            $table->json('meta')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes (IMPORTANT)
            |--------------------------------------------------------------------------
            */
            $table->index(['user_id', 'status']);
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
