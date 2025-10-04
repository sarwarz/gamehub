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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Core info
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('sku')->nullable()->unique();
            $table->longText('description')->nullable();

            // Single relations
            $table->foreignId('developer_id')->nullable()->constrained('product_developers')->nullOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained('product_publishers')->nullOnDelete();

            // Media
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();

            // Extra metadata
            $table->json('attributes')->nullable();
            $table->json('system_requirements')->nullable();
            $table->string('delivery_type')->default('instant')->comment('instant / manual / email / link');

            // Visibility & status
            $table->enum('status', ['draft','active','inactive','archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();


            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
