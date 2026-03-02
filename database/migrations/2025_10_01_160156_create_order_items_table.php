<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_offer_id')->constrained()->cascadeOnDelete();

            // Price snapshot
            $table->integer('quantity');
            $table->decimal('unit_price', 14, 2);
            $table->decimal('subtotal', 14, 2);

            // Delivery
            $table->enum('delivery_type', ['auto','manual'])->default('auto');
            $table->enum('delivery_status', ['pending','delivered','failed'])->default('pending');

            // Status
            $table->enum('status', ['active','refunded','cancelled'])->default('active');

            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['seller_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
