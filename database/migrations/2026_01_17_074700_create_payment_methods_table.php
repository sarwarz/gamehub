<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('name');               // PayPal, Stripe, Razorpay
            $table->string('code')->unique();     // paypal, stripe, razorpay
            $table->string('type')->default('online'); 
            // online | offline (bank, cod)

            // Status & mode
            $table->boolean('is_enabled')->default(false);
            $table->enum('mode', ['sandbox', 'live'])->default('sandbox');

            // Localization
            $table->string('country')->nullable();     // United States
            $table->string('currency')->nullable();    // USD
            $table->decimal('rate', 12, 6)->default(1); // Per USD rate

            // Dynamic credentials & settings
            $table->json('config')->nullable();

            // Sorting in UI
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
