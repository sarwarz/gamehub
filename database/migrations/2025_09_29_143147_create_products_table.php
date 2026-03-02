<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('sku')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->string('short_description', 500)->nullable();

            $table->string('image')->nullable();
            $table->json('gallery')->nullable();

            $table->foreignId('developer_id')->nullable()
                ->constrained('product_developers')->nullOnDelete();

            $table->foreignId('publisher_id')->nullable()
                ->constrained('product_publishers')->nullOnDelete();

            $table->foreignId('label_id')->nullable()
                ->constrained('product_labels')->nullOnDelete();

            $table->json('attributes')->nullable();
            $table->json('system_requirements')->nullable();
            $table->string('delivery_type')
                ->default('instant')
                ->comment('instant / manual / email / link');

            $table->enum('status', ['active','inactive'])
                ->default('active');

            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index(['status', 'is_featured']);
        });

        DB::statement("
            ALTER TABLE products
            ADD FULLTEXT INDEX ft_products_search (title, sku)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
