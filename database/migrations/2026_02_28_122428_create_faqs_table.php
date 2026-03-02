<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_category_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->longText('answer');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['faq_category_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
