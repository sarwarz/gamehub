<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();

            $table->decimal('gross_amount', 12, 2);
            $table->decimal('commission', 12, 2);
            $table->decimal('net_amount', 12, 2);

            $table->enum('status', ['pending', 'held', 'available', 'paid'])->default('pending');
            $table->timestamp('escrow_expires_at')->nullable();
            $table->timestamp('escrow_released_at')->nullable();
            $table->timestamp('balance_credited_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_earnings');
    }
};
