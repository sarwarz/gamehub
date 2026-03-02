<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 30);
            $table->string('event_id')->nullable();
            $table->string('event_type')->nullable();
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->enum('status', ['received', 'processing', 'processed', 'failed'])->default('received');
            $table->text('error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'event_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
