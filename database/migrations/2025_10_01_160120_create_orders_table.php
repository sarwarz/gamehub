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

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('order_number')->unique();
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

            // Payment
            $table->string('payment_method')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('payment_reference')->nullable();
            $table->enum('payment_status', ['pending','paid','failed','refunded'])->default('pending');

            // Order lifecycle
            $table->enum('status', ['pending','processing','completed','cancelled','refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
