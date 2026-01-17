<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('blog_category_id')
                  ->constrained('blog_categories')
                  ->cascadeOnDelete();

            // Core content
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');

            // Media
            $table->string('featured_image')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // Publishing
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();

            // Engagement (future-ready)
            $table->unsignedInteger('views')->default(0);

            // Admin
            $table->unsignedInteger('position')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
