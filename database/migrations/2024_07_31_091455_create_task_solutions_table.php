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
        Schema::create('task_solutions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('taskee_id');
            $table->uuid('task_id');
            $table->foreign('taskee_id')->references('id')->on('taskees')->restrictOnDelete();
            $table->foreign('task_id')->references('id')->on('tasks')->restrictOnDelete();
            $table->string('title', 255)->nullable();
            $table->string('github', 255)->nullable();
            $table->string('live_github', 255)->nullable();
            $table->enum('status', ['Chưa nộp', 'Đã nộp', 'Đã xem', 'Đạt', 'Chưa đạt'])->default('Chưa nộp');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_solutions');
    }
};
