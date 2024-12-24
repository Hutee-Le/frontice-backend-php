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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('taskee_id');
            $table->unsignedBigInteger('service_id');
            $table->uuid('discount_id')->nullable();
            $table->string('order_id');
            $table->string('transaction_id')->nullable();
            $table->foreign('taskee_id')->references('id')->on('taskees')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('discount_id')->references('id')->on('discounts')->onDelete('set null');
            $table->timestamp('expired');
            $table->timestamp('gold_expired');
            $table->integer('amount_paid');
            $table->string('payment_method', 50);
            $table->enum('status', ['pending', 'success', 'fail'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
