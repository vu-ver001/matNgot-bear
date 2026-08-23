<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    // Trang tổng quan của quản trị viên
    // Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Quản lý danh mục
    // Route::resource('categories', CategoryController::class)->except(['show']);

    // Quản lý sản phẩm
    // Route::resource('products', ProductController::class)->except(['show']);

    // Quản lý mã giảm giá
    // Route::resource('vouchers', VoucherController::class)->except(['show']);

    // Quản lý đơn hàng
    // Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    // Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Quản lý người dùng
    // Route::get('/users', [UserController::class, 'index'])->name('users.index');
    // Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.updateStatus');

    // Quản lý đánh giá
    // Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    // Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});
