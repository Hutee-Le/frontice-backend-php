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
        Schema::create('task_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('task_solution_id');
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('task_solution_id')->references('id')->on('task_solutions')->restrictOnDelete();
            $table->string('comment_id');
            $table->uuid('parent_id')->nullable();
            $table->text('content');
            $table->integer('left');
            $table->integer('right');
            $table->boolean('is_edit')->default(false);
            $table->boolean('is_remove')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_comments');
    }
};
