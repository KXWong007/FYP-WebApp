<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForecastingTable extends Migration
{
    public function up()
    {
        Schema::create('forecasting', function (Blueprint $table) {
            $table->id();
            $table->string('inventoryId', 50); // Foreign key to Inventory
            $table->string('itemName', 50); // Item name from Inventory
            $table->double('dailyUsage'); // Daily usage in units
            $table->string('measurementUnit', 10); // Measurement unit
            $table->date('date'); // Date of usage record
            $table->timestamps();

            $table->foreign('inventoryId')->references('inventoryId')->on('inventory')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('forecasting');
    }
}

