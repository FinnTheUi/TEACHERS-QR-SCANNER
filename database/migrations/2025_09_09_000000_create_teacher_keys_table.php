<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teacher_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('key_code')->unique(); // Unique QR code identifier
            $table->string('description')->nullable(); // Optional description for the key
            $table->boolean('is_active')->default(true); // To enable/disable specific keys
            $table->timestamp('last_used_at')->nullable(); // Track when the key was last used
            $table->timestamp('expires_at')->nullable(); // Optional expiration date
            $table->timestamps(); // created_at and updated_at
            $table->softDeletes(); // Adds deleted_at column for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_keys');
    }
};
