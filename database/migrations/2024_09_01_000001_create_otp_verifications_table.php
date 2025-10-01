<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type', 50); // login, password_reset, email_verification, etc.
            $table->string('otp_hash');
            $table->string('session_token');
            $table->timestamp('expires_at');
            $table->integer('attempts')->default(0);
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['expires_at']);
            $table->index(['session_token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};