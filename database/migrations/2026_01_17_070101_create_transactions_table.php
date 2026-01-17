<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Who this transaction belongs to
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('seller_id')
                ->nullable()
                ->constrained('sellers')
                ->nullOnDelete();

            // Polymorphic reference (order, withdraw, refund, etc.)
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            // Transaction identity
            $table->string('trx')->unique(); // unique transaction code

            // Financials
            $table->decimal('amount', 14, 2);
            $table->decimal('fee', 14, 2)->default(0);
            $table->decimal('net_amount', 14, 2);

            // Currency
            $table->string('currency', 10)->default('USD');

            // Credit / Debit
            $table->enum('type', ['credit', 'debit']);

            // Transaction category
            $table->enum('category', [
                'order',
                'withdraw',
                'commission',
                'refund',
                'bonus',
                'adjustment',
                'payout'
            ]);

            // Status
            $table->enum('status', [
                'pending',
                'completed',
                'failed',
                'reversed'
            ])->default('pending');

            // Metadata
            $table->string('payment_method')->nullable();
            $table->string('gateway')->nullable();
            $table->text('description')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
            $table->index(['seller_id', 'category']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
