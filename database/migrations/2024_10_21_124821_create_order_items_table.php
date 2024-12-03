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
        Schema::create('orderItems', function (Blueprint $table) {
            $table->increments('orderItemId');
            $table->string('orderId'); 
            $table->foreign('orderId')->references('orderId')->on('orders')->onDelete('cascade');
            $table->string('dishId');
            $table->foreign('dishId')->references('dishId')->on('menu')->onDelete('cascade');
            $table->string('servedBy')->nullable();;
            $table->foreign('servedBy')->references('staffId')->on('staffs')->onDelete('cascade')->nullable(); 
            $table->integer('quantity');
            $table->string('remark')->nullable(); ;
            $table->string('status');
            $table->timestamp('start_time')->nullable(); // Cooking start time
            $table->timestamp('finishcook_time')->nullable(); // Cooking finish time
            $table->timestamp('servedtime')->nullable(); // Time when served
            $table->string('staffId', 255)->nullable(); // Nullable
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orderItems');
    }
};
