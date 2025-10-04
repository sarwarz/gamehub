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
        Schema::create('product_languages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // English, French, German, etc.
            $table->string('slug')->unique(); // english, french, german
            $table->string('code', 10)->unique()->nullable(); // en, fr, de, es
            $table->enum('status', ['active','inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_languages');
    }
};
