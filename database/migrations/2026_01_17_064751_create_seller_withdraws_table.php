<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_withdraws', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seller_id')
                ->constrained('sellers')
                ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);

            $table->string('method');
            $table->json('payment_details')->nullable();

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
            ])->default('pending');

            $table->text('note')->nullable();
            $table->text('admin_note')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_withdraws');
    }
};
