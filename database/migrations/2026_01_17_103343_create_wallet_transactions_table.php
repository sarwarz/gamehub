<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('wallet_id')
                ->constrained()
                ->cascadeOnDelete();

            // Amount
            $table->decimal('amount', 15, 2);

            // credit | debit
            $table->enum('type', ['credit', 'debit']);

            // Purpose
            $table->string('source')->nullable(); 
            // examples: order, refund, withdrawal, bonus, adjustment

            // Optional reference
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            // polymorphic-like without overhead

            // Meta
            $table->string('description')->nullable();

            $table->timestamps();

            // Performance indexes
            $table->index(['wallet_id', 'type']);
            $table->index(['reference_id', 'reference_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
