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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
//            $table->foreignId('role_id')->references('id')->on('roles');
            $table->string('firstname', 50);
            $table->string('lastname', 50);
            $table->date('birthdate')->nullable();
            $table->string('email', 256);
//            $table->unique(['role_id', 'email']);
            $table->string('phone_number', 13);
//            $table->unique(['role_id', 'phone_number']);
            $table->string('password');
            $table->rememberToken()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
