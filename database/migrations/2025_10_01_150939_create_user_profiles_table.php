<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();

            // Link to user
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Basic info
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('avatar')->nullable(); // profile picture
            $table->date('dob')->nullable(); // date of birth
            $table->enum('gender', ['male','female','other'])->nullable();

            // Contact
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();

            // Address
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();

            // eCommerce-related
            $table->string('company')->nullable(); // B2B customers
            $table->string('tax_id')->nullable(); // VAT/GST
            $table->boolean('newsletter_subscribed')->default(false); // marketing
            $table->boolean('is_verified')->default(false); // KYC

            // Preferences
            $table->string('preferred_currency', 10)->default('USD');
            $table->string('preferred_language', 10)->default('en');

            // Tracking
            $table->timestamp('last_login_at')->nullable();
            $table->ipAddress('last_login_ip')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
