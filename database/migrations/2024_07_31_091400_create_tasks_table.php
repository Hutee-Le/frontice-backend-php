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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tasker_id');
            $table->foreign('tasker_id')->references('id')->on('taskers')->restrictOnDelete();
            $table->string('title', 255);
            $table->string('image', 255);
            $table->json('technical');
            $table->string('source', 255);
            $table->string('figma', 255)->nullable();
            $table->integer('required_point');
            $table->string('short_des', 255);
            $table->json('desc');
            $table->timestamp('expired');
            $table->enum('status', ['pending', 'valid', 'deleted'])->nullable()->default(null);
            $table->timestamps();
            $table->boolean('is_skip')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
