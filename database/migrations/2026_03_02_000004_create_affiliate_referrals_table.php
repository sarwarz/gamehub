<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('landing_page', 500)->nullable();
            $table->string('referral_source', 30)->default('link');
            $table->enum('status', ['clicked', 'registered', 'converted'])->default('clicked');
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->index(['affiliate_id', 'status']);
            $table->index('ip_address');
            $table->index('referred_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_referrals');
    }
};
