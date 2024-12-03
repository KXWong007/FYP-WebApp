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
        Schema::create('staffs', function (Blueprint $table) {
            $table->string('staffId')->primary();
            $table->string('staffType');
            $table->string('name');
            $table->string('password');
            $table->string('email')->unique();
            $table->string('gender');
            $table->string('religion');
            $table->string('race');
            $table->string('nric');
            $table->string('profilePicture');
            $table->date('dateOfBirth');
            $table->string('phone')->unique();
            $table->text('address')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staffs');
    }
};
