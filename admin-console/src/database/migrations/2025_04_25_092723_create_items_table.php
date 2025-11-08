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
        Schema::create('items', function (Blueprint $table) {
            $table->id();                               // id: PRIMARY KEY, AUTO_INCREMENT
            $table->string('name', 255)->unique();     // 商品名
            $table->integer('price');                   // 価格
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict'); // カテゴリID（NULL不可）
            $table->integer('stock_quantity');          // 在庫数
            $table->timestamp('created_at')->useCurrent();              // 登録日時
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // 更新日時
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
