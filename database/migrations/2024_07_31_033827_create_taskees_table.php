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
        Schema::create('taskees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
            $table->string('firstname', 50);
            $table->string('lastname', 50);
            $table->string('phone', 11)->nullable();
            $table->string('github', 255)->nullable();
            $table->text('bio')->nullable();
            $table->string('cv')->nullable();
            $table->integer('points')->default(0);
            $table->timestamp('gold_expired')->nullable();
            $table->timestamp('gold_registration_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taskees');
    }
};
