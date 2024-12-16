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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id(); // Đây sẽ là INT UNSIGNED AUTO_INCREMENT
            $table->uuid('from')->nullable();
            $table->uuid('to')->nullable();
            $table->uuid('comment_id')->nullable();
            $table->uuid('challenge_comment_id')->nullable();
            $table->uuid('challenge_solution_id')->nullable();
            $table->uuid('task_id')->nullable();
            $table->uuid('task_solution_id')->nullable();
            $table->string('type');
            $table->string('message');
            $table->enum('status', ['seen', 'unseen'])->default('unseen');
            $table->timestamps();

            // Định nghĩa các khóa ngoại
            $table->foreign('from')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('comment_id')->references('id')->on('task_comments')->onDelete('cascade');
            $table->foreign('challenge_comment_id')->references('id')->on('comments')->onDelete('cascade');
            $table->foreign('challenge_solution_id')->references('id')->on('challenge_solutions')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('task_solution_id')->references('id')->on('task_solutions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
