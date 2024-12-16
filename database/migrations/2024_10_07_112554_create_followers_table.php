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
        Schema::create('followers', function (Blueprint $table) {
            $table->id();
            $table->uuid('tasker_id');
            $table->uuid('taskee_id');
            $table->unique(['taskee_id', 'tasker_id']);
            $table->foreign('tasker_id')->references('id')->on('taskers')->cascadeOnDelete();
            $table->foreign('taskee_id')->references('id')->on('taskees')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followers');
    }
};
