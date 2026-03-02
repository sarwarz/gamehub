<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 50)->default('Home');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('company', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address_line1', 255);
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100);
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100);
            $table->boolean('is_default')->default(false);
            $table->enum('type', ['billing', 'shipping', 'both'])->default('both');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
