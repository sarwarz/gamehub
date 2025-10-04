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
        Schema::create('seller_offers', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Retail Pricing
            $table->decimal('retail_price', 10, 2)->nullable();   // final product price
            $table->decimal('retail_acquisition_cost', 10, 2)->nullable();    // acquisition cost

            // Wholesale Pricing
            $table->decimal('wholesale_10_99_price', 10, 2)->nullable();
            $table->decimal('wholesale_10_99_acquisition_cost', 10, 2)->nullable();

            $table->decimal('wholesale_100_plus_price', 10, 2)->nullable();
            $table->decimal('wholesale_100_acquisition_cost', 10, 2)->nullable();

            // Sale mode
            $table->enum('sale_mode', ['retail', 'wholesale', 'both'])->default('retail');

            // Status
            $table->enum('status', ['draft', 'active', 'inactive', 'suspended'])->default('draft');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_promoted')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_offers');
    }
};
