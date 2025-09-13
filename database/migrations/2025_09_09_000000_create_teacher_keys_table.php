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
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('The ID of the teacher who owns this key');
            
            $table->string('key_code', 100)
                ->unique()
                ->comment('Unique QR code identifier');
            
            $table->string('description')
                ->nullable()
                ->comment('Optional description of the key purpose');
            
            $table->boolean('is_active')
                ->default(true)
                ->comment('Whether the key is currently active');
            
            $table->timestamp('last_used_at')
                ->nullable()
                ->comment('When the key was last scanned');
            
            $table->timestamp('expires_at')
                ->nullable()
                ->comment('Optional expiration date for the key');
            
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for commonly queried columns
            $table->index('is_active');
            $table->index('expires_at');
            $table->index('last_used_at');
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
