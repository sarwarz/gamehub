<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('referral_id')->nullable()->constrained('affiliate_referrals')->nullOnDelete();
            $table->decimal('order_amount', 14, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 14, 2);
            $table->enum('level', ['l1', 'l2'])->default('l1');
            $table->enum('status', ['pending', 'held', 'available', 'paid', 'reversed'])->default('pending');
            $table->timestamp('held_at')->nullable();
            $table->timestamp('available_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();

            $table->index(['affiliate_id', 'status']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
