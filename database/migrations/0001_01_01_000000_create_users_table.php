<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('forgot_password_at')->nullable();
            $table->string('password')->nullable();
            $table->enum('role', ['admin', 'tasker', 'taskee']);
            $table->string('image')->nullable();
            $table->string('github_id')->nullable()->unique();
            $table->char('otp', 6)->nullable();
            $table->timestamp('otp_expired')->nullable();
            $table->char('token_reset_password', 60)->nullable();
            $table->timestamp('token_reset_expired')->nullable();
            $table->char('token_verify_password', 60)->nullable();
            $table->timestamp('token_verify_expired')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
