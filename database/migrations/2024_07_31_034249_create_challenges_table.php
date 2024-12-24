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
        Schema::create('challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('admin_id');
            $table->unsignedBigInteger('level_id');
            $table->foreign('level_id')->references('id')->on('levels')->restrictOnDelete();
            $table->foreign('admin_id')->references('id')->on('admins')->restrictOnDelete();
            $table->string('title', 255);
            $table->string('image', 255);
            $table->json('technical');
            $table->string('source', 255);
            $table->string('figma', 255)->nullable();
            $table->integer('point');
            $table->string('short_des', 255);
            $table->json('desc');
            $table->boolean('is_deleted')->default(false);
            $table->boolean('premium')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
