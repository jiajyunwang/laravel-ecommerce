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
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('total_amount');
            $table->enum('status',['unhandled','shipping','completed','cancel'])->default('unhandled');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->string('name');
            $table->string('phone');
            $table->text('address');
            $table->longText('note')->nullable();
            $table->string('payment_method');
            $table->unsignedInteger('sub_total');
            $table->unsignedInteger('shipping_fee');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
