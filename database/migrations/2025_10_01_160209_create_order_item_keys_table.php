<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_keys', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('seller_offer_key_id')->nullable()->constrained()->onDelete('set null');

            $table->string('key_type')->nullable();  // text, image
            $table->text('key_value')->nullable();  // actual license key or file path

            $table->enum('status', ['assigned', 'delivered', 'refunded'])->default('assigned');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_keys');
    }
};
