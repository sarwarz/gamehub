<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 20)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('assigned_admin_id')->nullable();
            $table->string('department', 30);
            $table->string('subject');
            $table->string('priority', 20)->default('medium');
            $table->string('status', 30)->default('open');
            $table->boolean('is_escalated')->default(false);
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_reply_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('assigned_admin_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['status', 'priority']);
            $table->index('department');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
