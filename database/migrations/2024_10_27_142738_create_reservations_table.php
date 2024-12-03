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
        Schema::create('reservations', function (Blueprint $table) {
            $table->string('reservationId', 35)->primary();
            $table->integer ('orderId')->nullable();
            $table->string('customerId', 4)->nullable();
            $table->integer ('paymentId')->nullable();
            $table->dateTime('reservationDate');
            $table->integer ('pax');
            $table->string('eventType', 100)->nullable();
            $table->string('rarea', 35)->nullable();
            $table->string('remark', 100)->nullable();
            $table->string('reservedBy', 35);
            $table->string('rstatus', 35);
            $table->timestamp('status_updated_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps(); // Includes created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
