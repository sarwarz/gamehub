<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            // Polymorphic relation
            $table->morphs('mediable'); 
            // mediable_id | mediable_type

            // File info
            $table->string('disk')->default('public');
            $table->string('directory')->nullable();
            $table->string('filename');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('extension', 20)->nullable();

            // Media type
            $table->enum('type', ['image', 'video', 'audio', 'document', 'other'])
                  ->default('image');

            // Size & meta
            $table->unsignedBigInteger('size')->nullable(); // bytes
            $table->json('meta')->nullable(); // width, height, duration, etc.

            // Image specific
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
