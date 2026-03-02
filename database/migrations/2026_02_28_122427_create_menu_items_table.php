<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->nullOnDelete();
            $table->string('title');
            $table->enum('type', ['link', 'megamenu', 'heading'])->default('link');
            $table->unsignedTinyInteger('columns')->default(4);
            $table->string('url')->default('#');
            $table->string('icon')->nullable();
            $table->string('badge_text', 50)->nullable();
            $table->string('badge_color', 20)->nullable();
            $table->enum('target', ['_self', '_blank'])->default('_self');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
