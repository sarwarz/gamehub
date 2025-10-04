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
        Schema::create('seller_offer_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_offer_id')->constrained('seller_offers')->cascadeOnDelete();
            $table->enum('type', ['text', 'image'])->default('text'); // key format
            $table->longText('value'); // text code or image path
            $table->enum('status', ['available', 'sold', 'reserved'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_offer_keys');
    }
};
