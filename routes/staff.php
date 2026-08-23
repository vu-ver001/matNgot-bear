<?php

use Illuminate\Support\Facades\Route;

Route::prefix('staff')->name('staff.')->middleware(['auth', 'role:STAFF'])->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    // Trang tổng quan của nhân viên
    // Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Quản lý đơn hàng
    // Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    // Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    // Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    // Xác nhận thanh toán
    // Route::patch('/payments/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus');

    // Trò chuyện với khách hàng
    // Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    // Route::post('/chat/{conversation}', [ChatController::class, 'send'])->name('chat.send');
});
