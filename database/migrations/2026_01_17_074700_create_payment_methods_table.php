<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->unique();
            $table->string('type')->default('online');

            $table->boolean('is_enabled')->default(false);
            $table->enum('mode', ['sandbox', 'live'])->default('sandbox');

            $table->string('currency')->nullable();

            $table->json('config')->nullable();

            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
