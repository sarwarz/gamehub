<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('available_balance', 14, 2)->default(0);
            $table->decimal('pending_balance', 14, 2)->default(0);
            $table->decimal('total_earned', 14, 2)->default(0);
            $table->decimal('total_paid', 14, 2)->default(0);
            $table->decimal('total_reversed', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_balances');
    }
};
