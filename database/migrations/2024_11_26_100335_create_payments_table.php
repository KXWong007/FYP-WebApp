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
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('paymentId');
            $table->string('reservationId', 35);
            $table->integer('paymentreservationcode');
            $table->decimal('amount', 10, 2);
            $table->string('paymentType', 35);
            $table->dateTime('paymentDate');
            $table->string('paymentMethod', 35);
            $table->string('proofPayment', 255)->nullable();
            $table->timestamps();
            
            // Foreign key constraint for reservationId (optional)
            // Uncomment if you have a reservations table and want to enforce a relationship
            // $table->foreign('reservationId')->references('reservationId')->on('reservations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
