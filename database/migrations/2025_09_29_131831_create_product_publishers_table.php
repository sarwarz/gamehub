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
        Schema::create('product_publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ubisoft, EA, Activision
            $table->string('slug')->unique(); // ubisoft, ea, activision
            $table->string('website')->nullable(); // optional website
            $table->text('description')->nullable(); // optional about publisher
            $table->enum('status', ['active','inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_publishers');
    }
};
