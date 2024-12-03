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
        Schema::create('menu', function (Blueprint $table) {
            $table->string('dishId')->primary();
            $table->string('dishName');
            $table->string('category');
            $table->string('subcategory');
            $table->string('cuisine');
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('availableTime');
            $table->string('availableArea');
            $table->boolean('availability');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};
