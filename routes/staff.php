<?php

use Illuminate\Support\Facades\Route;

Route::prefix('staff')->name('staff.')->middleware(['auth', 'role:STAFF'])->group(function () {
    // Dashboard
    // Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Order management
    // Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    // Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    // Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    // Payment confirmation
    // Route::patch('/payments/{payment}/status', [PaymentController::class, 'updateStatus'])->name('payments.updateStatus');

    // Chat
    // Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    // Route::post('/chat/{conversation}', [ChatController::class, 'send'])->name('chat.send');
});
