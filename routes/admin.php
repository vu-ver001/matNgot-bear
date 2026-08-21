<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    // Dashboard
    // Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Category management
    // Route::resource('categories', CategoryController::class)->except(['show']);

    // Product management
    // Route::resource('products', ProductController::class)->except(['show']);

    // Voucher management
    // Route::resource('vouchers', VoucherController::class)->except(['show']);

    // Order management
    // Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    // Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // User management
    // Route::get('/users', [UserController::class, 'index'])->name('users.index');
    // Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.updateStatus');

    // Review management
    // Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    // Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});
