<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\NfcController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderDetailController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Laravelのauthミドルウェアが使うルート名 "login" を明示的に設定
Route::get('/login', fn () => redirect('/admin'))->name('login');

// 管理者入口：/admin
Route::get('/admin', function () {
    return Auth::guard('admin')->check()
        ? redirect()->route('admin.dashboard')
        : view('admin.login');
});

// ログイン・ログアウト処理（POST）
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

// ログイン必須の管理画面機能（すべて auth:admin で保護）
Route::prefix('admin')->middleware('auth:admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::get('users/{user}/orders', [UserController::class, 'showOrders'])->name('admin.users.orders');

    Route::get('/items', [ItemController::class, 'index'])->name('admin.items.index');
    Route::get('/items/create', [ItemController::class, 'create'])->name('admin.items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('admin.items.store');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('admin.items.edit');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('admin.items.update');

    Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::put('/admin/order-details/batch-cancel-or-paid', [OrderDetailController::class, 'batchCancelOrPaid'])->name('admin.order_details.batch_cancel_or_paid');
});

// NFC読み取り
Route::post('/admin/nfc/proxy-read', [NfcController::class, 'proxyRead'])->name('admin.nfc.proxyRead');
