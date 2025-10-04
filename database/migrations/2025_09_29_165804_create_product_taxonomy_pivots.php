<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // product ↔ categories
        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'category_id']);
        });

        // product ↔ platforms
        Schema::create('platform_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained('product_platforms')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'platform_id']);
        });

        // product ↔ types
        Schema::create('product_type_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('type_id')->constrained('product_types')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'type_id']);
        });

        // product ↔ regions
        Schema::create('product_region_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('product_regions')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'region_id']);
        });

        // product ↔ languages
        Schema::create('language_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('product_languages')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'language_id']);
        });

        // product ↔ works_on (OS/Devices)
        Schema::create('product_works_on_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('works_on_id')->constrained('product_works_on')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'works_on_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('platform_product');
        Schema::dropIfExists('product_type_product');
        Schema::dropIfExists('product_region_product');
        Schema::dropIfExists('language_product');
        Schema::dropIfExists('product_works_on_product');
    }
};
