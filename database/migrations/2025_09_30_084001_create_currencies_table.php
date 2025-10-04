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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();   // e.g. EUR, USD, GBP
            $table->string('name');                // Euro, US Dollar, etc.
            $table->string('symbol', 5)->nullable(); // €, $, £
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // Only one true (e.g. EUR)
            $table->decimal('rate', 15, 8)->default(1.0); // e.g. 1.06543210
            $table->timestamp('fetched_at')->nullable(); // when it was last updated
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
