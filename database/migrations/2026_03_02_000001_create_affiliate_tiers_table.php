<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('commission_rate', 5, 2)->default(5.00);
            $table->decimal('l2_commission_rate', 5, 2)->default(0);
            $table->decimal('min_earnings_threshold', 14, 2)->default(0);
            $table->integer('min_referrals')->default(0);
            $table->integer('min_conversions')->default(0);
            $table->string('color', 30)->default('secondary');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_tiers');
    }
};
