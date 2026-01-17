<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();

            // Tax identity
            $table->string('name'); // e.g. VAT, GST, Sales Tax
            $table->string('code')->nullable(); // e.g. VAT-UK

            // Scope
            $table->foreignId('seller_id')
                ->nullable()
                ->constrained('sellers')
                ->nullOnDelete(); // null = global tax

            // Location based
            $table->string('country', 2)->nullable(); // ISO code (US, UK, BD)
            $table->string('state')->nullable();      // CA, NY, etc
            $table->string('city')->nullable();

            // Tax calculation
            $table->enum('type', ['percent', 'fixed']);
            $table->decimal('rate', 8, 4); // percent OR fixed amount

            // Priority & behavior
            $table->integer('priority')->default(1);
            $table->boolean('is_compound')->default(false);

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
