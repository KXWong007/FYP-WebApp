<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTable extends Migration
{
    public function up()
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->string('inventoryId', 50)->primary(); // Primary Key as String
            $table->string('itemName', 50); // Not Null
            $table->integer('quantity'); // Not Null
            $table->integer('minimum'); // Not Null
            $table->integer('maximum'); // Not Null
            $table->decimal('unitPrice', 10, 2); // Not Null
            $table->string('measurementUnit', 10); // Not Null
            
            $table->timestamps(); // Optional: Created at & Updated at timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory');
    }
}
