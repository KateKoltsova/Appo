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
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->after('paid_sum')
                ->nullable()
                ->constrained('orders', 'id')
                ->cascadeOnDelete();
        });
        $order = \App\Models\Order::first();
        DB::table('appointments')->update([
            'order_id' => $order->id,
        ]);

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
    }
};
