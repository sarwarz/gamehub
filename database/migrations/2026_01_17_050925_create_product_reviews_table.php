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
        Schema::create('product_reviews', function (Blueprint $table) {

            $table->id();

            // Relations
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Review content
            $table->unsignedTinyInteger('rating') // 1–5
                ->comment('Rating from 1 to 5');

            $table->string('title')->nullable();
            $table->text('review')->nullable();

            // Moderation
            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            $table->boolean('is_verified_purchase')
                ->default(false)
                ->comment('True if user purchased this product');

            // Anti-spam / analytics
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['product_id', 'status']);
            $table->index(['user_id']);
            $table->unique(['product_id', 'user_id']); // 1 review per user per product
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
