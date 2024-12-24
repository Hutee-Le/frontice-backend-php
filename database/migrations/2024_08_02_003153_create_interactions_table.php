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
        Schema::create('interactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('taskee_id');
            $table->uuid('challenge_solution_id');
            $table->unique(['taskee_id', 'challenge_solution_id']);
            $table->foreign('challenge_solution_id')->references('id')->on('challenge_solutions')->cascadeOnDelete();
            $table->foreign('taskee_id')->references('id')->on('taskees')->cascadeOnDelete();
            $table->enum('type', ['like', 'dislike']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
};
