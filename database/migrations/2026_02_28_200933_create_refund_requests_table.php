<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['full', 'partial'])->default('full');
            $table->decimal('amount', 12, 2);
            $table->string('reason');
            $table->text('description')->nullable();
            $table->json('evidence')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'processing', 'completed'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['seller_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
