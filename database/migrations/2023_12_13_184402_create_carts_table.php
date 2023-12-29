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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained('schedules', 'id')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services', 'id')->cascadeOnDelete();
            $table->foreignId('price_id')->constrained('prices', 'id')->cascadeOnDelete();
            $table->unique(['schedule_id', 'client_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
