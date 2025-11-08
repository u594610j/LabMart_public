<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade'); // 注文IDに紐付け
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict');  // 商品IDに紐付け

            // 購入時スナップショット
            $table->string('item_name', 255);
            $table->integer('item_price');
            $table->integer('item_quantity');
            $table->string('item_category', 100)->nullable();

            $table->boolean('paid')->default(false); // 支払い済みフラグ
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
