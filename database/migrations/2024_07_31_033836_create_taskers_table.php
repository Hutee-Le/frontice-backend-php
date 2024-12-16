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
        Schema::create('taskers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
            $table->uuid('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
            $table->boolean('is_approved')->default(false);
            $table->string('firstname', 50);
            $table->string('lastname', 50);
            $table->string('phone', 11)->nullable();
            $table->string('company', 255);
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taskers');
    }
};
