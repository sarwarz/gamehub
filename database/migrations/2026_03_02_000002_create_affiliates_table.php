<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('referral_code', 20)->unique();
            $table->enum('status', ['pending', 'active', 'suspended', 'rejected'])->default('pending');
            $table->string('tier', 30)->default('bronze');
            $table->string('payment_method', 50)->nullable();
            $table->json('payment_details')->nullable();
            $table->text('bio')->nullable();
            $table->string('website')->nullable();
            $table->string('social_media')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('tier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};
