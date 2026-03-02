<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['price_drop', 'back_in_stock'])->default('price_drop');
            $table->decimal('target_price', 14, 2)->nullable();
            $table->decimal('last_notified_price', 14, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'product_id', 'type']);
            $table->index(['product_id', 'type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_alerts');
    }
};
