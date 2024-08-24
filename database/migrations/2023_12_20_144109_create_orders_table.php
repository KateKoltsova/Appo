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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->unsignedInteger('total');
            $table->string('payment');
            $table->string('payment_status')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        $user = \App\Models\User::first();
        if (!is_null($user)) {
            if (!is_null($user->id)) {
                DB::table('orders')->insert([
                    'user_id' => $user->id,
                    'total' => 0,
                    'payment' => 'full',
                    'payment_status' => 'success',
                    'description' => 'Default successful payment order',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
