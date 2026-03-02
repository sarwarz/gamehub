<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('amount', 15, 2);
            $table->enum('type', ['credit', 'debit']);

            $table->string('source')->nullable();

            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();

            $table->string('description')->nullable();

            $table->enum('status', ['pending', 'completed', 'failed'])
                ->default('completed');

            $table->decimal('balance_after', 15, 2)->nullable();

            $table->timestamps();

            $table->index(['wallet_id', 'type']);
            $table->index(['reference_id', 'reference_type']);
            $table->index('status');
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
