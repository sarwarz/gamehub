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
        Schema::create('product_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained('product_platforms')->cascadeOnDelete();
            $table->foreignId('type_id')->constrained('product_types')->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('product_regions')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('product_languages')->cascadeOnDelete();
            $table->foreignId('works_on_id')->constrained('product_works_on')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('source_url')->nullable(); 
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'completed'
            ])->default('pending'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_requests');
    }
};
