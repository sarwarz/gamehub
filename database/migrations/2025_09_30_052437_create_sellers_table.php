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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();

            // Relation with users table (seller account login)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Store details
            $table->string('store_name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->text('description')->nullable();

            // Contact info
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            // Business / Legal info
            $table->string('company_name')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('tax_id')->nullable();

            // Location
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();


            // Performance & status
            $table->decimal('rating', 3, 2)->default(0.00); // avg rating 0.00 - 5.00
            $table->unsignedBigInteger('total_sales')->default(0);
            $table->unsignedBigInteger('total_products')->default(0);
            $table->enum('status', ['pending', 'active', 'suspended'])->default('pending');
            $table->boolean('is_verified')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
