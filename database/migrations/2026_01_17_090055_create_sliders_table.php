<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['hero', 'banner', 'promotional', 'product_spotlight'])
                  ->default('hero');

            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('badge_text', 50)->nullable();
            $table->string('badge_color', 20)->nullable();

            $table->string('image');
            $table->string('overlay_color', 50)->nullable();
            $table->enum('text_color', ['light', 'dark'])->default('light');
            $table->enum('text_position', ['left', 'center', 'right'])->default('left');

            $table->foreignId('product_id')
                  ->nullable()
                  ->constrained('products')
                  ->nullOnDelete();

            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();

            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('views')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
