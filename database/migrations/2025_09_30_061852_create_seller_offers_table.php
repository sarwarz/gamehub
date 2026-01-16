<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_offers', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('seller_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            // Retail Pricing
            $table->decimal('retail_price', 10, 2)->nullable();
            $table->decimal('retail_acquisition_cost', 10, 2)->nullable();

            // Wholesale Pricing
            $table->decimal('wholesale_10_99_price', 10, 2)->nullable();
            $table->decimal('wholesale_10_99_acquisition_cost', 10, 2)->nullable();

            $table->decimal('wholesale_100_plus_price', 10, 2)->nullable();
            $table->decimal('wholesale_100_acquisition_cost', 10, 2)->nullable();

            // Sale mode
            $table->enum('sale_mode', ['retail', 'wholesale', 'both'])
                ->default('retail');

            // Status & flags
            $table->enum('status', ['draft', 'active', 'inactive', 'suspended'])
                ->default('draft');

            $table->boolean('is_verified')->default(false);
            $table->boolean('is_promoted')->default(false);

            $table->timestamps();

            // ⚡ PERFORMANCE INDEXES
            $table->index('product_id');
            $table->index('status');

            // 🚀 FAST price lookup for live search
            $table->index(
                ['product_id', 'status', 'retail_price'],
                'idx_offer_product_status_price'
            );

            // Optional: verified/promoted sorting
            $table->index(
                ['product_id', 'is_verified', 'is_promoted'],
                'idx_offer_flags'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_offers');
    }
};
