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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buy_order_id')
              ->constrained('orders')
              ->cascadeOnDelete();

            $table->foreignId('sell_order_id')
              ->constrained('orders')
              ->cascadeOnDelete();

            $table->foreignId('buyer_id')
              ->constrained('users')
              ->cascadeOnDelete();

            $table->foreignId('seller_id')
              ->constrained('users')
              ->cascadeOnDelete();

            $table->string('symbol');

            $table->decimal('price', 18, 8);
            $table->decimal('amount', 18, 8);

            $table->decimal('volume_usd', 18, 8);

            $table->decimal('fee_usd_buyer', 18, 8)->default(0);
            $table->decimal('fee_usd_seller', 18, 8)->default(0);

            $table->timestamps();

            $table->index(['symbol', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
