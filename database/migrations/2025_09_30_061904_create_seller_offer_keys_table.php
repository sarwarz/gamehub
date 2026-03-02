<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_offer_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_offer_id')->constrained('seller_offers')->cascadeOnDelete();
            $table->enum('type', ['text', 'image'])->default('text');
            $table->longText('value');
            $table->enum('status', ['available', 'sold', 'reserved'])->default('available');
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('reserved_until')->nullable();
            $table->unsignedBigInteger('reserved_session_id')->nullable();
            $table->timestamps();

            $table->index(['status', 'reserved_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_offer_keys');
    }
};
