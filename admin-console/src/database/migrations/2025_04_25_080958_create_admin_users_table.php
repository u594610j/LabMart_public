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
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();                         // 自動増分 ID
            $table->string('name')->unique();     // 管理者名（重複不可）
            $table->string('email')->unique();    // メールアドレス（重複不可）
            $table->string('password');           // ハッシュ化されたパスワード
            $table->rememberToken();              // ログイン状態を保持するためのtoken
            $table->timestamp('last_login_at')->nullable(); // 最後にログインした日時
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();                 // deleted_at カラム（論理削除用）
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
