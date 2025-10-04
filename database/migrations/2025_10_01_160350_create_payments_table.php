<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Payment gateway details
            $table->string('payment_method')->nullable(); // stripe, paypal, wallet
            $table->string('provider')->nullable();       // gateway provider name
            $table->string('transaction_id')->nullable()->unique();

            // Amounts
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->string('currency', 10)->default('USD');

            // Status
            $table->enum('status', [
                'pending', 'processing', 'paid', 'failed', 'refunded'
            ])->default('pending');

            // Metadata
            $table->json('meta')->nullable(); // store raw gateway response

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
