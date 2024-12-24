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
        Schema::create('challenge_solutions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('challenge_id')->nullable();;
            $table->foreign('challenge_id')->references('id')->on('challenges')->onDelete('set null');
            $table->uuid('taskee_id');
            $table->foreign('taskee_id')->references('id')->on('taskees')->onDelete('cascade');
            $table->uuid('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->nullOnDelete();
            $table->string('title', 255)->nullable();
            $table->string('github', 255)->nullable();
            $table->string('live_github', 255)->nullable();
            $table->text('pride_of')->nullable();
            $table->text('challenge_overcome')->nullable();
            $table->text('help_with')->nullable();
            $table->enum('status', ['pointed', 'pending', 'valid', 'deleted'])->nullable()->default(null);
            $table->text('mentor_feedback')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['taskee_id', 'challenge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_solutions');
    }
};
