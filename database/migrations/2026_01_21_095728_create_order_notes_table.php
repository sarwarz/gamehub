<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_notes', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete(); // admin / customer / system

            // Note details
            $table->text('note');
            $table->enum('type', ['system', 'admin', 'customer'])->default('system');

            // Visibility
            $table->boolean('is_visible_to_customer')->default(false);

            $table->timestamps();

            // Indexes
            $table->index(['order_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_notes');
    }
};
