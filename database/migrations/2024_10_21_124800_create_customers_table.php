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
        Schema::create('customers', function (Blueprint $table) {
            $table->string('customerId')->primary();
            $table->string('customerType');
            $table->string('name');
            $table->string('password');
            $table->string('email')->unique();
            $table->string('gender');
            $table->string('religion');
            $table->string('race');
            $table->string('nric');
            $table->string('profilePicture')->nullable();
            $table->date('dateOfBirth');
            $table->string('phoneNum')->unique();
            $table->text('address')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        DB::table('customers')->update(['profilePicture' => 'https://yourdomain.com/path/to/real/profile/image.jpg']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
