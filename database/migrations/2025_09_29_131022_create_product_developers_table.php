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
        Schema::create('product_developers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ubisoft, Rockstar, Mojang
            $table->string('slug')->unique(); // ubisoft, rockstar, mojang
            $table->string('website')->nullable(); // optional developer website
            $table->text('description')->nullable(); // optional about developer
            $table->enum('status', ['active','inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_developers');
    }
};
